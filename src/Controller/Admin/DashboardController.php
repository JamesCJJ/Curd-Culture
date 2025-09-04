<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class DashboardController extends AppController
{
    public function index()
    {
        $ContactMessages = $this->fetchTable('ContactMessages');

        $total        = $ContactMessages->find()->count();
        $unreadCount  = $ContactMessages->find()->where(['status' => 'unread'])->count();
        $repliedCount = $ContactMessages->find()->where(['status IN' => ['read', 'in_progress', 'closed']])->count();
        $todayCount   = $ContactMessages->find()
            ->where(function ($exp, $q) {
                return $exp->gte('created', (new \DateTime('today'))->format('Y-m-d 00:00:00'));
            })
            ->count();

        $latest = $ContactMessages->find()
            ->orderByDesc('created')
            ->limit(10)
            ->all();

        $this->set(compact('total','unreadCount','repliedCount','todayCount','latest'));
    }
}
