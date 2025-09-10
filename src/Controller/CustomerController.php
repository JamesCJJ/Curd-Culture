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
        $Addresses = $this->fetchTable('Addresses');
        
        $user = $Users->get($userId);
        $addresses = $Addresses->find()
            ->where(['user_id' => $userId])
            ->order(['is_default' => 'DESC', 'created' => 'ASC'])
            ->all();
        
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
        
        $this->set(compact('user', 'addresses'));
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
     * Add new address
     */
    public function addAddress()
    {
        $this->request->allowMethod(['post']);
        
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Addresses = $this->fetchTable('Addresses');
        $address = $Addresses->newEmptyEntity();
        
        $data = $this->request->getData();
        $data['user_id'] = $userId;
        $data['type'] = 'billing'; // Default type
        
        $address = $Addresses->patchEntity($address, $data);
        
        if ($Addresses->save($address)) {
            $this->Flash->success('Address added successfully.');
        } else {
            $this->Flash->error('Unable to add address. Please check the form.');
        }
        
        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Edit address
     */
    public function editAddress($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Addresses = $this->fetchTable('Addresses');
        $address = $Addresses->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->first();
            
        if (!$address) {
            $this->Flash->error('Address not found.');
            return $this->redirect(['action' => 'profile']);
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $address = $Addresses->patchEntity($address, $data);
            
            if ($Addresses->save($address)) {
                $this->Flash->success('Address updated successfully.');
                return $this->redirect(['action' => 'profile']);
            } else {
                $this->Flash->error('Unable to update address.');
            }
        }
        
        $this->set(compact('address'));
    }

    /**
     * Delete address
     */
    public function deleteAddress($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Addresses = $this->fetchTable('Addresses');
        $address = $Addresses->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->first();
            
        if (!$address) {
            $this->Flash->error('Address not found.');
        } elseif ($Addresses->delete($address)) {
            $this->Flash->success('Address deleted successfully.');
        } else {
            $this->Flash->error('Unable to delete address.');
        }
        
        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Set default address
     */
    public function setDefaultAddress($id = null)
    {
        $this->request->allowMethod(['post']);
        
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');
        
        $Addresses = $this->fetchTable('Addresses');
        
        // First, unset all default addresses for this user
        $Addresses->updateAll(
            ['is_default' => false],
            ['user_id' => $userId]
        );
        
        // Then set the selected address as default
        $address = $Addresses->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->first();
            
        if (!$address) {
            $this->Flash->error('Address not found.');
        } else {
            $address->is_default = true;
            if ($Addresses->save($address)) {
                $this->Flash->success('Default address updated.');
            } else {
                $this->Flash->error('Unable to update default address.');
            }
        }
        
        return $this->redirect(['action' => 'profile']);
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
