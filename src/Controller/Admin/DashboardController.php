<?php
// File: src/Controller/Admin/DashboardController.php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * Admin Dashboard Controller
 * - Gathers small summary stats for Contacts, Products, Orders, and Users.
 * - Feeds the Dashboard view with lightweight aggregates and latest items.
 */
class DashboardController extends AppController
{
    public function index()
    {
        // === Contact Messages Stats ===
        $ContactMessages = $this->fetchTable('ContactMessages');
        $contactStats = [
            'total'  => $ContactMessages->find()->count(),
            'unread' => $ContactMessages->find()->where(['status' => 'unread'])->count(),
            'read'   => $ContactMessages->find()->where(['status' => 'read'])->count(),
            'today'  => $ContactMessages->find()
                ->where(function ($exp) {
                    // Messages created today from 00:00 (server time)
                    return $exp->gte('created', (new \DateTime('today'))->format('Y-m-d 00:00:00'));
                })
                ->count(),
        ];

        $latestMessages = $ContactMessages->find()
            ->orderByDesc('ContactMessages.created')
            ->limit(5)
            ->all();

        // === Products Stats ===
        $Products = $this->fetchTable('Products');
        $productStats = [
            'total'        => $Products->find()->count(),
            'in_stock'     => $Products->find()->where(['stock >' => 0])->count(),
            'low_stock'    => $Products->find()->where(['stock <=' => 10, 'stock >' => 0])->count(),
            'out_of_stock' => $Products->find()->where(['stock' => 0])->count(),
        ];

        // === Orders Stats ===
        $Orders = $this->fetchTable('Orders');
        $orderStats = [
            'total'     => $Orders->find()->count(),
            'pending'   => $Orders->find()->where(['status' => 'pending'])->count(),
            'completed' => $Orders->find()->where(['status' => 'completed'])->count(),
            'total_revenue' => (float)$Orders->find()
                ->where(['status' => 'completed'])
                ->select(function ($query) {
                    // Sum of 'total' for completed orders
                    return ['sum' => $query->func()->sum('total')];
                })
                ->enableHydration(false)
                ->first()['sum'] ?: 0.0,
        ];

        $latestOrders = $Orders->find()
            ->contain(['Users'])
            ->orderByDesc('Orders.created')
            ->limit(5)
            ->all();

        // === Users Stats ===
        $Users = $this->fetchTable('Users');
        $userStats = [
            'total'     => $Users->find()->count(),
            'customers' => $Users->find()->where(['role' => 'customer'])->count(),
            'admins'    => $Users->find()->where(['role' => 'admin'])->count(),
            'active'    => $Users->find()->where(['status' => 'active'])->count(),
        ];

        // === Recent Activity (last day/week) ===
        $recentActivity = [
            'new_orders_today' => $Orders->find()
                ->where(function ($exp) {
                    return $exp->gte('created', (new \DateTime('today'))->format('Y-m-d 00:00:00'));
                })
                ->count(),
            'new_users_week' => $Users->find()
                ->where(function ($exp) {
                    return $exp->gte('created', (new \DateTime('-7 days'))->format('Y-m-d 00:00:00'));
                })
                ->count(),
            'revenue_today' => (float)$Orders->find()
                ->where([
                    'status' => 'completed',
                    function ($exp) {
                        return $exp->gte('created', (new \DateTime('today'))->format('Y-m-d 00:00:00'));
                    }
                ])
                ->select(function ($query) {
                    return ['sum' => $query->func()->sum('total')];
                })
                ->enableHydration(false)
                ->first()['sum'] ?: 0.0,
        ];

        // Convenient individual vars for the template
        $total       = $contactStats['total'];
        $unreadCount = $contactStats['unread'];
        $readCount   = $contactStats['read'];
        $todayCount  = $contactStats['today'];
        $latest      = $latestMessages;

        $this->set(compact(
            'contactStats', 'latestMessages', 'latest',
            'productStats', 'orderStats', 'latestOrders',
            'userStats', 'recentActivity',
            'total', 'unreadCount', 'readCount', 'todayCount'
        ));
    }
}
