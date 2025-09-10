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
     * Index method - List all orders
     */
    public function index()
    {
        $query = trim((string)$this->request->getQuery('q'));
        $status = (string)$this->request->getQuery('status');
        $paymentStatus = (string)$this->request->getQuery('payment_status');
        $from = $this->request->getQuery('from');
        $to = $this->request->getQuery('to');
        
        $table = $this->fetchTable('Orders');
        
        $ordersQuery = $table->find()
            ->contain(['Users'])
            ->orderByDesc('Orders.created');
            
        // Search functionality
        if ($query !== '') {
            $ordersQuery->where([
                'OR' => [
                    'Orders.email LIKE' => '%' . $query . '%',
                    'Orders.full_name LIKE' => '%' . $query . '%',
                    'Orders.id' => is_numeric($query) ? (int)$query : 0,
                ]
            ]);
        }
        
        // Filter by status
        if ($status !== '') {
            $ordersQuery->where(['Orders.status' => $status]);
        }
        
        // Filter by payment status
        if ($paymentStatus !== '') {
            $ordersQuery->where(['Orders.payment_status' => $paymentStatus]);
        }
        
        // Date range filter
        if (!empty($from)) {
            $ordersQuery->where(['Orders.created >=' => new DateTime($from . ' 00:00:00')]);
        }
        if (!empty($to)) {
            $ordersQuery->where(['Orders.created <=' => new DateTime($to . ' 23:59:59')]);
        }
        
        // Pagination
        $limit = 20;
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;
        
        $orders = $ordersQuery->limit($limit)->offset($offset)->all();
        $totalCount = $ordersQuery->count();
        $totalPages = (int)ceil($totalCount / $limit);
        
        // Statistics
        $stats = [
            'total' => $table->find()->count(),
            'pending' => $table->find()->where(['status' => 'pending'])->count(),
            'processing' => $table->find()->where(['status' => 'processing'])->count(),
            'completed' => $table->find()->where(['status' => 'completed'])->count(),
            'cancelled' => $table->find()->where(['status' => 'cancelled'])->count(),
            'total_revenue' => (float)$table->find()
                ->where(['status' => 'completed'])
                ->select(function ($q) { return ['sum' => $q->func()->sum('total')]; })
                ->enableHydration(false)
                ->first()['sum'] ?? 0,
        ];
        
        $pagination = [
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
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
            $data = $this->request->getData();
            
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
            
            if ($table->save($order)) {
                $this->Flash->success(__('Order status updated successfully.'));
            } else {
                $this->Flash->error(__('Unable to update order status.'));
            }
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
     */
    public function analytics()
    {
        $ordersTable = $this->fetchTable('Orders');
        
        // Recent orders (last 30 days)
        $recentOrders = $ordersTable->find()
            ->where(['created >=' => (new DateTime())->modify('-30 days')])
            ->count();
            
        // Revenue by month (last 12 months)
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = (new DateTime())->modify("-{$i} months");
            $startOfMonth = $date->modify('first day of this month')->format('Y-m-d 00:00:00');
            $endOfMonth = $date->modify('last day of this month')->format('Y-m-d 23:59:59');
            
            $revenue = (float)$ordersTable->find()
                ->where([
                    'status' => 'completed',
                    'created >=' => $startOfMonth,
                    'created <=' => $endOfMonth
                ])
                ->select(function ($q) { return ['sum' => $q->func()->sum('total')]; })
                ->enableHydration(false)
                ->first()['sum'] ?? 0;
                
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue ?: 0
            ];
        }
        
        // Top selling products
        $topProducts = $this->fetchTable('OrderItems')
            ->find()
            ->contain(['Products'])
            ->select([
                'product_id',
                'Products.name',
                'total_qty' => $this->fetchTable('OrderItems')->find()->func()->sum('qty'),
                'total_revenue' => $this->fetchTable('OrderItems')->find()->func()->sum('line_total')
            ])
            ->group(['product_id'])
            ->orderByDesc('total_qty')
            ->limit(10)
            ->all();
        
        $this->set(compact('recentOrders', 'monthlyRevenue', 'topProducts'));
    }
}
