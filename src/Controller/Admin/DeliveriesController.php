<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use Cake\I18n\FrozenDate;

/**
 * Admin Deliveries Controller
 *
 * Features:
 * - Daily delivery board grouped by time slots
 * - CSV export for the selected day
 * - Bulk status updates
 * - Move an order to another date/slot with a basic capacity guard
 *
 * DB assumptions:
 * - orders: fulfillment_method VARCHAR('delivery'|'pickup'), delivery_date DATE,
 *           delivery_slot_id INT NULL, pickup_location_id INT NULL
 * - delivery_slots: id, name, window_start TIME, window_end TIME, capacity INT NULL, is_active TINYINT(1)
 */
class DeliveriesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * GET /admin/deliveries?date=YYYY-MM-DD&export=csv
     */
    public function index()
    {
        $this->request->allowMethod(['get']);

        // Selected date (defaults to today)
        $dateStr = (string)($this->request->getQuery('date') ?? (new FrozenDate())->format('Y-m-d'));
        try {
            $date = new FrozenDate($dateStr);
        } catch (\Throwable $e) {
            $date = new FrozenDate();
        }

        $Orders        = $this->fetchTable('Orders');
        $DeliverySlots = $this->fetchTable('DeliverySlots');

        // Active slots -> AS ARRAY (avoid Entity in the view)
        $slots = $DeliverySlots->find()
            ->select(['id','name','window_start','window_end','capacity','is_active'])
            ->where(['is_active' => 1])
            ->order(['window_start' => 'ASC', 'id' => 'ASC'])
            ->enableHydration(false)          // ← 关键：返回 array 而不是 Entity
            ->all()
            ->toArray();

        // All delivery orders for that date (Entities are fine here)
        $ordersQ = $Orders->find()
            ->where([
                'DATE(Orders.delivery_date)' => $date->format('Y-m-d'),
                'Orders.fulfillment_method'  => 'delivery',
            ])
            ->contain(['DeliverySlots', 'OrderItems'])
            ->order(['Orders.delivery_slot_id' => 'ASC', 'Orders.created' => 'ASC']);

        // CSV export
        if (strtolower((string)$this->request->getQuery('export')) === 'csv') {
            $fh = fopen('php://temp', 'r+');
            fputcsv($fh, [
                'Order ID','Customer','Email','Address','City','Postcode',
                'Total (AUD)','Status','Fulfillment','Slot','Window Start','Window End','Created'
            ]);
            foreach ($ordersQ as $o) {
                $slotName = $o->delivery_slot->name ?? '';
                $ws = $o->delivery_slot->window_start ?? '';
                $we = $o->delivery_slot->window_end ?? '';
                fputcsv($fh, [
                    $o->id,
                    (string)($o->full_name ?? ''),
                    (string)($o->email ?? ''),
                    (string)($o->address ?? ''),
                    (string)($o->city ?? ''),
                    (string)($o->postcode ?? ''),
                    number_format((float)($o->total ?? 0), 2),
                    (string)$o->status,
                    (string)($o->fulfillment_method ?? 'delivery'),
                    $slotName, $ws, $we,
                    $o->created ? $o->created->format('Y-m-d H:i:s') : '',
                ]);
            }
            rewind($fh);
            $csv = (string)stream_get_contents($fh);
            fclose($fh);

            return $this->response
                ->withType('csv')
                ->withDownload(sprintf('deliveries-%s.csv', $date->format('Ymd')))
                ->withStringBody($csv);
        }

        // Build groups for the board (all arrays)
        $groups = [];
        foreach ($slots as $s) {
            $capacity = $s['capacity'] === null ? null : (int)$s['capacity'];
            $groups[(int)$s['id']] = [
                'slot'      => [
                    'id'           => (int)$s['id'],
                    'name'         => (string)$s['name'],
                    'window_start' => $s['window_start'],
                    'window_end'   => $s['window_end'],
                    'capacity'     => $capacity,
                ],
                'orders'    => [],
                'used'      => 0,
                'remaining' => $capacity,
            ];
        }
        // Unassigned bucket
        $groups[0] = [
            'slot'      => ['id' => 0, 'name' => 'Unassigned', 'window_start' => null, 'window_end' => null, 'capacity' => null],
            'orders'    => [],
            'used'      => 0,
            'remaining' => null,
        ];

        foreach ($ordersQ as $o) {
            $sid = (int)($o->delivery_slot_id ?? 0);

            if (!isset($groups[$sid])) {
                // A slot was turned inactive after assignment — still show it
                $slot = [
                    'id'           => $sid,
                    'name'         => $o->delivery_slot->name ?? ('Slot #' . $sid),
                    'window_start' => $o->delivery_slot->window_start ?? null,
                    'window_end'   => $o->delivery_slot->window_end ?? null,
                    'capacity'     => $o->delivery_slot->capacity ?? null,
                ];
                $groups[$sid] = [
                    'slot'      => $slot,
                    'orders'    => [],
                    'used'      => 0,
                    'remaining' => $slot['capacity'] !== null ? (int)$slot['capacity'] : null,
                ];
            }

            $groups[$sid]['orders'][] = $o;
            $groups[$sid]['used']++;
            if ($groups[$sid]['remaining'] !== null) {
                $groups[$sid]['remaining'] = max(0, $groups[$sid]['remaining'] - 1);
            }
        }

        $this->set(compact('date', 'slots', 'groups'));
    }

    /**
     * POST /admin/deliveries/bulk-update
     */
    public function bulkUpdate()
    {
        $this->request->allowMethod(['post']);

        $Orders   = $this->fetchTable('Orders');
        $orderIds = array_values(array_unique(array_map('intval', (array)$this->request->getData('order_ids'))));
        $status   = (string)$this->request->getData('status');

        $valid = ['pending','confirmed','processing','shipped','delivered','cancelled'];
        if (!in_array($status, $valid, true)) {
            $this->Flash->error('Invalid status.');
            return $this->redirect($this->referer(['action' => 'index'], true));
        }
        if (empty($orderIds)) {
            $this->Flash->info('No orders selected.');
            return $this->redirect($this->referer(['action' => 'index'], true));
        }

        $affected = $Orders->updateAll(['status' => $status], ['id IN' => $orderIds]);
        $this->Flash->success(sprintf('Updated %d order(s) to "%s".', (int)$affected, $status));
        return $this->redirect($this->referer(['action' => 'index'], true));
    }

    /**
     * POST /admin/deliveries/move
     */
    public function move()
    {
        $this->request->allowMethod(['post']);

        $Orders        = $this->fetchTable('Orders');
        $DeliverySlots = $this->fetchTable('DeliverySlots');

        $orderId = (int)$this->request->getData('order_id');
        $dateStr = (string)$this->request->getData('delivery_date');
        $slotId  = $this->request->getData('delivery_slot_id');
        $slotId  = ($slotId === '' || $slotId === null) ? null : (int)$slotId;

        if ($orderId <= 0) {
            $this->Flash->error('Invalid order ID.');
            return $this->redirect($this->referer(['action' => 'index'], true));
        }

        try {
            $newDate = new FrozenDate($dateStr);
        } catch (\Throwable $e) {
            $this->Flash->error('Invalid date.');
            return $this->redirect($this->referer(['action' => 'index'], true));
        }

        // Capacity guard
        if ($slotId) {
            $slot = $DeliverySlots->find()
                ->select(['id','capacity','is_active'])
                ->where(['id' => $slotId])
                ->first();

            if (!$slot || !$slot->is_active) {
                $this->Flash->error('Selected slot is not available.');
                return $this->redirect($this->referer(['action' => 'index'], true));
            }

            if ($slot->capacity !== null) {
                $assigned = $Orders->find()
                    ->where([
                        'DATE(Orders.delivery_date)' => $newDate->format('Y-m-d'),
                        'Orders.delivery_slot_id'    => $slotId,
                        'Orders.fulfillment_method'  => 'delivery',
                    ])->count();

                if ($assigned >= (int)$slot->capacity) {
                    $this->Flash->error('This slot is at capacity.');
                    return $this->redirect($this->referer(['action' => 'index'], true));
                }
            }
        }

        $order = $Orders->find()->where(['id' => $orderId])->first();
        if (!$order) {
            $this->Flash->error('Order not found.');
            return $this->redirect($this->referer(['action' => 'index'], true));
        }

        $order->delivery_date    = $newDate;
        $order->delivery_slot_id = $slotId;

        if ($Orders->save($order)) {
            $this->Flash->success('Order rescheduled.');
        } else {
            $this->Flash->error('Could not reschedule order.');
        }

        return $this->redirect($this->referer(['action' => 'index'], true));
    }
}
