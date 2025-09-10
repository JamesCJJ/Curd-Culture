<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class CustomerController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Paginator');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        
        // Require authentication for all customer actions
        $this->Authentication->requireIdentity();
        
        // Ensure only customers can access this controller
        $identity = $this->request->getAttribute('identity');
        $role = strtolower((string)($identity?->get('role') ?? ''));
        
        if ($role !== 'customer') {
            $this->Flash->error('Access denied. Customer access required.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Customer Dashboard - Orders page (default)
     */
    public function index()
    {
        return $this->redirect(['action' => 'orders']);
    }

    /**
     * Customer Orders listing
     */
    public function orders()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Orders = $this->fetchTable('Orders');
        
        // Build query conditions
        $conditions = ['Orders.user_id' => $userId];
        
        // Filter by status if provided
        $status = $this->request->getQuery('status');
        if (!empty($status)) {
            $conditions['Orders.status'] = $status;
        }
        
        // Filter by date range if provided
        $dateFrom = $this->request->getQuery('date_from');
        $dateTo = $this->request->getQuery('date_to');
        
        $query = $Orders->find()
            ->where($conditions)
            ->contain(['OrderItems'])
            ->order(['Orders.created' => 'DESC']);
            
        if (!empty($dateFrom)) {
            $query->where(['Orders.created >=' => $dateFrom]);
        }
        
        if (!empty($dateTo)) {
            $query->where(['Orders.created <=' => $dateTo . ' 23:59:59']);
        }
        
        $orders = $this->paginate($query);
        
        // Get status options for filter
        $statusOptions = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        
        $this->set(compact('orders', 'statusOptions', 'status', 'dateFrom', 'dateTo'));
    }

    /**
     * View specific order details
     */
    public function orderDetails($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Orders = $this->fetchTable('Orders');
        
        $order = $Orders->find()
            ->where([
                'Orders.id' => $id,
                'Orders.user_id' => $userId
            ])
            ->contain(['OrderItems' => ['Products']])
            ->first();
            
        if (!$order) {
            $this->Flash->error('Order not found.');
            return $this->redirect(['action' => 'orders']);
        }
        
        $this->set(compact('order'));
    }

    /**
     * Customer Profile management
     */
    public function profile()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Users = $this->fetchTable('Users');
        $user = $Users->get($userId);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Only allow certain fields to be updated
            $allowedFields = ['email'];
            
            $user = $Users->patchEntity($user, $data, [
                'fields' => $allowedFields
            ]);
            
            if ($Users->save($user)) {
                $this->Flash->success('Profile updated successfully.');
                return $this->redirect(['action' => 'profile']);
            } else {
                $this->Flash->error('Unable to update profile.');
            }
        }
        
        $this->set(compact('user'));
    }

    /**
     * Buy again - add order items to cart
     */
    public function buyAgain($orderId = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Orders = $this->fetchTable('Orders');
        $Carts = $this->fetchTable('Carts');
        $CartItems = $this->fetchTable('CartItems');
        
        // Verify order belongs to current user
        $order = $Orders->find()
            ->where([
                'Orders.id' => $orderId,
                'Orders.user_id' => $userId
            ])
            ->contain(['OrderItems'])
            ->first();
            
        if (!$order) {
            $this->Flash->error('Order not found.');
            return $this->redirect(['action' => 'orders']);
        }
        
        // Get or create active cart
        $cart = $Carts->find()
            ->where([
                'user_id' => $userId,
                'status' => 'open'
            ])
            ->first();
            
        if (!$cart) {
            $cart = $Carts->newEntity([
                'user_id' => $userId,
                'status' => 'open',
                'currency' => 'AUD'
            ]);
            $Carts->save($cart);
        }
        
        // Add order items to cart
        $addedCount = 0;
        foreach ($order->order_items as $orderItem) {
            if ($orderItem->product_id) {
                // Check if item already exists in cart
                $existingCartItem = $CartItems->find()
                    ->where([
                        'cart_id' => $cart->id,
                        'product_id' => $orderItem->product_id
                    ])
                    ->first();
                    
                if ($existingCartItem) {
                    // Update quantity
                    $existingCartItem->qty += $orderItem->qty;
                    $CartItems->save($existingCartItem);
                } else {
                    // Create new cart item
                    $cartItem = $CartItems->newEntity([
                        'cart_id' => $cart->id,
                        'product_id' => $orderItem->product_id,
                        'qty' => $orderItem->qty,
                        'price' => $orderItem->price,
                        'currency' => $orderItem->currency
                    ]);
                    $CartItems->save($cartItem);
                }
                $addedCount++;
            }
        }
        
        if ($addedCount > 0) {
            $this->Flash->success(sprintf('%d items added to cart.', $addedCount));
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        } else {
            $this->Flash->error('No items could be added to cart.');
            return $this->redirect(['action' => 'orders']);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->Authentication->logout();
        $this->Flash->success('Signed out successfully.');
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }
}
