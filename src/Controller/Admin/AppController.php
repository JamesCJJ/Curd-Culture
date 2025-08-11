<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Require authentication for all admin by default.
        // Controllers can allow specific actions.
        $this->Authentication->addUnauthenticatedActions([]);
    }
}
