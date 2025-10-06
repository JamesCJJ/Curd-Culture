<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;

class PickupsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();


        $this->loadComponent('Flash'); // CakePHP 5 仍可用
    }

    /** GET /admin/pickups */
    public function index()
    {
        $this->request->allowMethod(['get']);

        $q      = trim((string)$this->request->getQuery('q', ''));
        $status = (string)$this->request->getQuery('status', '');

        $conditions = [];
        if ($q !== '') {
            $conditions['OR'] = [
                'PickupLocations.name LIKE'            => "%{$q}%",
                'PickupLocations.address_line_1 LIKE'  => "%{$q}%",
                'PickupLocations.address_line_2 LIKE'  => "%{$q}%",
                'PickupLocations.suburb LIKE'          => "%{$q}%",
                'PickupLocations.state LIKE'           => "%{$q}%",
                'PickupLocations.postcode LIKE'        => "%{$q}%",
            ];
        }
        if ($status === 'active') {
            $conditions['PickupLocations.is_active'] = 1;
        } elseif ($status === 'inactive') {
            $conditions['PickupLocations.is_active'] = 0;
        }

        $PickupLocations = $this->fetchTable('PickupLocations');


        $pickups = $PickupLocations->find()
            ->where($conditions)
            ->orderBy(['PickupLocations.modified' => 'DESC', 'PickupLocations.id' => 'DESC'])
            ->all();


        $stats = [
            'total'    => (int)$PickupLocations->find()->count(),
            'active'   => (int)$PickupLocations->find()->where(['is_active' => 1])->count(),
            'inactive' => (int)$PickupLocations->find()->where(['is_active' => 0])->count(),
        ];

        $this->set(compact('pickups', 'q', 'status', 'stats'));
    }

    /** GET|POST /admin/pickups/add */
    public function add()
    {
        $this->request->allowMethod(['get', 'post']);

        $PickupLocations = $this->fetchTable('PickupLocations');
        $pickup = $PickupLocations->newEmptyEntity();

        if ($this->request->is('post')) {
            $pickup = $PickupLocations->patchEntity($pickup, $this->request->getData());
            if ($PickupLocations->save($pickup)) {
                $this->Flash->success('Pickup location has been created.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Failed to create pickup location. Please check the form.');
        }

        $this->set(compact('pickup'));
        $this->render('form');
    }

    /** GET|POST|PUT|PATCH /admin/pickups/edit/{id} */
    public function edit($id = null)
    {
        $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        $PickupLocations = $this->fetchTable('PickupLocations');

        try {
            $pickup = $PickupLocations->get((int)$id);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Pickup not found.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $pickup = $PickupLocations->patchEntity($pickup, $this->request->getData());
            if ($PickupLocations->save($pickup)) {
                $this->Flash->success('Pickup location has been updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Failed to update pickup location. Please fix the errors.');
        }

        $this->set(compact('pickup'));
        $this->render('form');
    }

    /** POST /admin/pickups/toggle/{id}  -> enable/disable */
    public function toggle($id = null)
    {
        $this->request->allowMethod(['post']);

        $PickupLocations = $this->fetchTable('PickupLocations');

        try {
            $pickup = $PickupLocations->get((int)$id);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Pickup not found.');
            return $this->redirect(['action' => 'index']);
        }

        $pickup->is_active = (int)!$pickup->is_active;

        if ($PickupLocations->save($pickup)) {
            $this->Flash->success($pickup->is_active ? 'Enabled.' : 'Disabled.');
        } else {
            $this->Flash->error('Failed to toggle status.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
