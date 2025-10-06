<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

/**
 * Admin - Pickup Locations management
 *
 * Table: pickup_locations (id, name, address, city, postcode, country, notes, is_active, created, modified)
 */
class PickupsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    /** GET /admin/pickups */
    public function index()
    {
        $this->request->allowMethod(['get']);
        $Locations = $this->fetchTable('PickupLocations');

        $q      = trim((string)$this->request->getQuery('q', ''));
        $status = (string)$this->request->getQuery('status', '');

        $conditions = [];
        if ($q !== '') {
            $conditions['OR'] = [
                'PickupLocations.name LIKE'    => '%' . $q . '%',
                'PickupLocations.address LIKE' => '%' . $q . '%',
                'PickupLocations.city LIKE'    => '%' . $q . '%',
                'PickupLocations.postcode LIKE'=> '%' . $q . '%',
            ];
        }
        if ($status === 'active')   { $conditions['is_active'] = 1; }
        if ($status === 'inactive') { $conditions['is_active'] = 0; }

        $stats = [
            'total'    => $Locations->find()->count(),
            'active'   => $Locations->find()->where(['is_active' => 1])->count(),
            'inactive' => $Locations->find()->where(['is_active' => 0])->count(),
        ];

        $limit  = 20;
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $query = $Locations->find()->where($conditions)->order(['name' => 'ASC', 'id' => 'ASC']);

        $totalCount = $query->count();
        $locations = $query->limit($limit)->offset($offset)->all();

        $pagination = [
            'page'       => $page,
            'totalPages' => (int)ceil($totalCount / $limit),
            'totalCount' => $totalCount,
            'hasPrev'    => $page > 1,
            'hasNext'    => $page * $limit < $totalCount,
        ];

        $this->set(compact('locations', 'q', 'status', 'stats', 'pagination'));
    }

    /** GET|POST /admin/pickups/add */
    public function add()
    {
        $Locations = $this->fetchTable('PickupLocations');
        $loc = $Locations->newEmptyEntity();

        if ($this->request->is(['post'])) {
            $data = (array)$this->request->getData();
            $loc  = $Locations->patchEntity($loc, $data);

            if ($Locations->save($loc)) {
                $this->Flash->success('Pickup location created.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Could not create location.');
        }

        $this->set(compact('loc'));
    }

    /** GET|POST /admin/pickups/edit/:id */
    public function edit(int $id)
    {
        $Locations = $this->fetchTable('PickupLocations');
        $loc = $Locations->get($id);

        if ($this->request->is(['post','put','patch'])) {
            $loc = $Locations->patchEntity($loc, (array)$this->request->getData());
            if ($Locations->save($loc)) {
                $this->Flash->success('Pickup location updated.');
                return $this->redirect(['action'=>'index']);
            }
            $this->Flash->error('Could not update location.');
        }

        $this->set(compact('loc'));
    }

    /** POST /admin/pickups/toggle/:id */
    public function toggle(int $id)
    {
        $this->request->allowMethod(['post']);
        $Locations = $this->fetchTable('PickupLocations');
        $loc = $Locations->get($id);
        $loc->is_active = (int)!$loc->is_active;

        if ($Locations->save($loc)) {
            $this->Flash->success('Status updated.');
        } else {
            $this->Flash->error('Could not update status.');
        }
        return $this->redirect($this->referer(['action'=>'index'], true));
    }

    /** POST /admin/pickups/delete/:id */
    public function delete(int $id)
    {
        $this->request->allowMethod(['post','delete']);
        $Locations = $this->fetchTable('PickupLocations');
        $loc = $Locations->get($id);

        if ($Locations->delete($loc)) {
            $this->Flash->success('Location deleted.');
        } else {
            $this->Flash->error('Could not delete location.');
        }
        return $this->redirect(['action'=>'index']);
    }
}
