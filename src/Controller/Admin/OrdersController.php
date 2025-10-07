<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\DateTime;

/**
 * Admin Orders Controller
 * Order management system for administrators
 */
class OrdersController extends AppController
{
    /**
     * Build the "completed" condition:
     * Completed := (delivered + paid) OR (refunded)
     */
    private function completedWhere(): array
    {
        return [
            'OR' => [
                ['status' => 'delivered', 'payment_status' => 'paid'],
                ['payment_status' => 'refunded'],
            ]
        ];
    }

    /**
     * Index method - List all orders
     */
    public function index()
    {
        $query         = trim((string)$this->request->getQuery('q'));
        $status        = (string)$this->request->getQuery('status');          // may be 'completed' (derived)
        $paymentStatus = (string)$this->request->getQuery('payment_status');
        $from          = $this->request->getQuery('from');
        $to            = $this->request->getQuery('to');

        $table = $this->fetchTable('Orders');

        $ordersQuery = $table->find()
            ->contain(['Users'])
            ->orderByDesc('Orders.created');

        // Search
        if ($query !== '') {
            $ordersQuery->where([
                'OR' => [
                    'Orders.email LIKE'     => '%' . $query . '%',
                    'Orders.full_name LIKE' => '%' . $query . '%',
                    'Orders.id'             => is_numeric($query) ? (int)$query : 0,
                ]
            ]);
        }

        // Filters
        if ($status !== '') {
            if ($status === 'completed') {
                // Derived status: (delivered + paid) OR (refunded)
                $ordersQuery->where($this->completedWhere());
            } else {
                $ordersQuery->where(['Orders.status' => $status]);
            }
        }

        if ($paymentStatus !== '') {
            $ordersQuery->where(['Orders.payment_status' => $paymentStatus]);
        }

        // Date range
        if (!empty($from)) {
            $ordersQuery->where(['Orders.created >=' => new DateTime($from . ' 00:00:00')]);
        }
        if (!empty($to)) {
            $ordersQuery->where(['Orders.created <=' => new DateTime($to . ' 23:59:59')]);
        }

        // Pagination (manual)
        $limit  = 20;
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $orders      = $ordersQuery->limit($limit)->offset($offset)->all();
        $totalCount  = $ordersQuery->count();
        $totalPages  = (int)ceil($totalCount / $limit);

        // Statistics
        $stats = [
            'total'      => $table->find()->count(),
            'pending'    => $table->find()->where(['status' => 'pending'])->count(),
            'processing' => $table->find()->where(['status' => 'processing'])->count(),
            'shipped'    => $table->find()->where(['status' => 'shipped'])->count(),
            'delivered'  => $table->find()->where(['status' => 'delivered'])->count(),
            'cancelled'  => $table->find()->where(['status' => 'cancelled'])->count(),

            // Completed (derived): (delivered+paid) OR refunded
            'completed'  => $table->find()->where($this->completedWhere())->count(),

            // REVENUE: recognize only for delivered + paid (refunds excluded)
            'total_revenue' => (float)($table->find()
                ->where(['status' => 'delivered', 'payment_status' => 'paid'])
                ->select(function ($q) { return ['sum' => $q->func()->sum('total')]; })
                ->enableHydration(false)
                ->first()['sum'] ?? 0),
        ];

        $pagination = [
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'hasNext'    => $page < $totalPages,
            'hasPrev'    => $page > 1,
        ];

        $this->set(compact('orders', 'pagination', 'stats', 'query', 'status', 'paymentStatus', 'from', 'to'));
    }

    /**
     * View method - Display order details
     */
    public function view($id = null)
    {
        $order = $this->fetchTable('Orders')->get($id, [
            'contain' => ['Users', 'OrderItems.Products']
        ]);

        $this->set(compact('order'));
    }

    /**
     * Edit method - Update order
     */
    public function edit($id = null)
    {
        $table = $this->fetchTable('Orders');
        $order = $table->get($id, [
            'contain' => ['OrderItems.Products']
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data  = $this->request->getData();
            $order = $table->patchEntity($order, $data);

            if ($table->save($order)) {
                $this->Flash->success(__('Order has been updated successfully.'));
                return $this->redirect(['action' => 'view', $order->id]);
            }

            $this->Flash->error(__('Unable to update order. Please check the form and try again.'));
        }

        $this->set(compact('order'));
    }

    /**
     * Update order status
     */
    public function updateStatus($id = null)
    {
        $this->request->allowMethod(['post', 'patch']);
        $table = $this->fetchTable('Orders');
        $order = $table->get($id);

        $data = $this->request->getData();

        if (!empty($data['status'])) {
            $order->status = $data['status'];
        }

        if (!empty($data['payment_status'])) {
            $order->payment_status = $data['payment_status'];

            if ($data['payment_status'] === 'paid' && !$order->paid_at) {
                $order->paid_at = DateTime::now();
            }
        }

        if ($table->save($order)) {
            $this->Flash->success(__('Order status updated successfully.'));
        } else {
            $this->Flash->error(__('Unable to update order status.'));
        }

        return $this->redirect(['action' => 'view', $order->id]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($id = null)
    {
        $this->request->allowMethod(['post', 'patch']);
        $table = $this->fetchTable('Orders');
        $order = $table->get($id);

        $data = $this->request->getData();

        if (!empty($data['payment_status'])) {
            $order->payment_status = $data['payment_status'];

            if ($data['payment_status'] === 'paid' && !$order->paid_at) {
                $order->paid_at = DateTime::now();
            }

            if ($table->save($order)) {
                $this->Flash->success(__('Payment status updated successfully.'));
            } else {
                $this->Flash->error(__('Unable to update payment status.'));
            }
        }

        return $this->redirect(['action' => 'view', $order->id]);
    }

    /**
     * Delete method - Remove order
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('Orders');
        $order = $table->get($id);

        if ($table->delete($order)) {
            $this->Flash->success(__('Order has been deleted successfully.'));
        } else {
            $this->Flash->error(__('Unable to delete order.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Export orders to CSV
     */
    public function export()
    {
        $this->disableAutoRender();

        $orders = $this->fetchTable('Orders')->find()
            ->contain(['Users'])
            ->select([
                'Orders.id', 'Orders.email', 'Orders.full_name', 'Orders.total',
                'Orders.currency', 'Orders.status', 'Orders.payment_status',
                'Orders.created', 'Orders.modified', 'Users.name'
            ])
            ->orderByDesc('Orders.created')
            ->all();

        $filename = 'orders_' . DateTime::now()->format('Ymd_His') . '.csv';

        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename);

        $out = fopen('php://temp', 'r+');
        fputcsv($out, [
            'Order ID', 'Customer Name', 'Email', 'Total', 'Currency',
            'Status', 'Payment Status', 'Order Date', 'Modified'
        ]);

        foreach ($orders as $order) {
            fputcsv($out, [
                $order->id,
                $order->full_name,
                $order->email,
                $order->total,
                $order->currency,
                $order->status,
                $order->payment_status,
                $order->created?->format('Y-m-d H:i:s') ?? '',
                $order->modified?->format('Y-m-d H:i:s') ?? '',
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response->withStringBody($csv);
    }

    /**
     * Dashboard analytics
     * - "recentOrders": count of COMPLETED (delivered+paid OR refunded) in last 30 days
     * - Monthly revenue: only delivered+paid in each month (refunds excluded)
     */
    public function analytics()
    {
        $ordersTable = $this->fetchTable('Orders');

        // Completed in last 30 days (using modified as "state-change" proxy)
        $recentOrders = $ordersTable->find()
            ->where($this->completedWhere())
            ->where(['modified >=' => (new DateTime())->modify('-30 days')])
            ->count();

        // Revenue by month (last 12 months), recognized on delivery (delivered+paid)
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthObj     = (new DateTime('now'))->modify("-{$i} months");
            $startOfMonth = (new DateTime($monthObj->format('Y-m-01 00:00:00')))->format('Y-m-d H:i:s');
            $endOfMonth   = (new DateTime($monthObj->format('Y-m-t 23:59:59')))->format('Y-m-d H:i:s');

            $row = $ordersTable->find()
                ->where([
                    'status'         => 'delivered',
                    'payment_status' => 'paid',
                    'modified >='    => $startOfMonth,
                    'modified <='    => $endOfMonth,
                ])
                ->select(function ($q) {
                    return ['sum' => $q->func()->sum('total')];
                })
                ->enableHydration(false)
                ->first();

            $monthlyRevenue[] = [
                'month'   => $monthObj->format('M Y'),
                'revenue' => (float)($row['sum'] ?? 0),
            ];
        }

        // Top selling products: only lines from delivered+paid orders
        // Order by total_revenue (qty * price) first, then by total_qty as tiebreaker
        $orderItems  = $this->fetchTable('OrderItems');
        $topProducts = $orderItems->find()
            ->contain(['Products'])
            ->matching('Orders', function ($q) {
                return $q->where([
                    'Orders.status'         => 'delivered',
                    'Orders.payment_status' => 'paid',
                ]);
            })
            ->select(function ($q) {
                $sumQty = $q->func()->sum('OrderItems.qty');
                $sumRev = $q->func()->sum($q->newExpr('OrderItems.price * OrderItems.qty'));
                return [
                    'product_id'     => 'OrderItems.product_id',
                    'Products__name' => 'Products.name',
                    'total_qty'      => $sumQty,
                    'total_revenue'  => $sumRev,
                ];
            })
            ->group(['OrderItems.product_id', 'Products.name'])
            ->orderByDesc('total_revenue')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->all();

        $this->set(compact('recentOrders', 'monthlyRevenue', 'topProducts'));
    }
}
