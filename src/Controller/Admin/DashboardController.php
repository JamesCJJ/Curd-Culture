<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class DashboardController extends AppController
{
    public function index()
    {
        $this->loadModel('ContactMessages');
        $total = $this->ContactMessages->find()->count();
        $today = $this->ContactMessages->find()
            ->where(function ($exp, $q) {
                $start = new \DateTimeImmutable('today');
                return $exp->gte('created', $start->format('Y-m-d 00:00:00'));
            })
            ->count();
        $this->set(compact('total','today'));
    }
}
