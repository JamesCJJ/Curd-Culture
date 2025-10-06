<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use Cake\I18n\FrozenTime;

/**
 * Admin - Delivery Slots management
 *
 * Table: delivery_slots (id, name, window_start, window_end, capacity, is_active, created, modified)
 */
class DeliverySlotsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    /** GET /admin/delivery-slots */
    public function index()
    {
        $this->request->allowMethod(['get']);

        $DeliverySlots = $this->fetchTable('DeliverySlots');

        // Filters
        $q      = trim((string)$this->request->getQuery('q', ''));
        $status = (string)$this->request->getQuery('status', '');

        $conditions = [];
        if ($q !== '') {
            $conditions['OR'] = [
                'DeliverySlots.name LIKE' => '%' . $q . '%',
            ];
        }
        if ($status === 'active')   { $conditions['is_active'] = 1; }
        if ($status === 'inactive') { $conditions['is_active'] = 0; }

        // Stats
        $stats = [
            'total'    => $DeliverySlots->find()->count(),
            'active'   => $DeliverySlots->find()->where(['is_active' => 1])->count(),
            'inactive' => $DeliverySlots->find()->where(['is_active' => 0])->count(),
        ];

        // Paging (simple)
        $limit  = 20;
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $query = $DeliverySlots->find()
            ->where($conditions)
            ->order(['window_start' => 'ASC', 'id' => 'ASC']);

        $totalCount = $query->count();
        $slots = $query->limit($limit)->offset($offset)->all();

        $pagination = [
            'page'       => $page,
            'totalPages' => (int)ceil($totalCount / $limit),
            'totalCount' => $totalCount,
            'hasPrev'    => $page > 1,
            'hasNext'    => $page * $limit < $totalCount,
        ];

        $this->set(compact('slots', 'q', 'status', 'stats', 'pagination'));
    }

    /** GET|POST /admin/delivery-slots/add */
    public function add()
    {
        $DeliverySlots = $this->fetchTable('DeliverySlots');
        $slot = $DeliverySlots->newEmptyEntity();

        if ($this->request->is(['post'])) {
            $data = (array)$this->request->getData();
            // Normalize empty capacity to null
            if ($data['capacity'] === '' || $data['capacity'] === null) {
                $data['capacity'] = null;
            }
            $slot = $DeliverySlots->patchEntity($slot, $data);

            if ($DeliverySlots->save($slot)) {
                $this->Flash->success('Delivery slot created.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not create slot. Please check the form.');
        }

        $this->set(compact('slot'));
    }

    /** GET|POST /admin/delivery-slots/edit/:id */
    public function edit(int $id)
    {
        $DeliverySlots = $this->fetchTable('DeliverySlots');
        $slot = $DeliverySlots->get($id);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = (array)$this->request->getData();
            if ($data['capacity'] === '' || $data['capacity'] === null) {
                $data['capacity'] = null;
            }
            $slot = $DeliverySlots->patchEntity($slot, $data);

            if ($DeliverySlots->save($slot)) {
                $this->Flash->success('Delivery slot updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not update slot.');
        }

        $this->set(compact('slot'));
    }

    /** POST /admin/delivery-slots/toggle/:id */
    public function toggle(int $id)
    {
        $this->request->allowMethod(['post']);
        $DeliverySlots = $this->fetchTable('DeliverySlots');
        $slot = $DeliverySlots->get($id);
        $slot->is_active = (int)!$slot->is_active;

        if ($DeliverySlots->save($slot)) {
            $this->Flash->success('Status updated.');
        } else {
            $this->Flash->error('Could not update status.');
        }
        return $this->redirect($this->referer(['action' => 'index'], true));
    }

    /** POST /admin/delivery-slots/delete/:id */
    public function delete(int $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $DeliverySlots = $this->fetchTable('DeliverySlots');
        $slot = $DeliverySlots->get($id);

        if ($DeliverySlots->delete($slot)) {
            $this->Flash->success('Slot deleted.');
        } else {
            $this->Flash->error('Could not delete slot.');
        }
        return $this->redirect(['action' => 'index']);
    }
}
