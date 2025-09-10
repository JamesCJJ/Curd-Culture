<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;

class CustomerController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please log in to access your account.');
            $event->setResult($this->redirect(['controller' => 'Users', 'action' => 'login']));
            return;
        }

        $role = $identity->get('role');
        error_log("Customer Controller - User role: " . ($role ?? 'null'));

        $allowedRoles = ['customer', 'user'];
        if ($role && !in_array(strtolower($role), $allowedRoles, true)) {
            $this->Flash->error('Access denied. Customer access required.');
            $event->setResult($this->redirect(['controller' => 'Users', 'action' => 'login']));
            return;
        }
    }

    /** Dashboard */
    public function index()
    {
        try {
            $identity = $this->request->getAttribute('identity');
            $this->set('user', $identity);
            error_log("Customer Dashboard accessed successfully");
        } catch (\Exception $e) {
            error_log("Customer Dashboard error: " . $e->getMessage());
            throw $e;
        }
    }

    /** Orders list */
    public function orders()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->get('id');

        $Orders = $this->fetchTable('Orders');

        $conditions = ['Orders.user_id' => $userId];

        $status = $this->request->getQuery('status');
        if (!empty($status)) {
            $conditions['Orders.status'] = $status;
        }

        $dateFrom = $this->request->getQuery('date_from');
        $dateTo   = $this->request->getQuery('date_to');

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

        $limit  = 10;
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $orders      = $query->limit($limit)->offset($offset)->all();
        $totalCount  = $query->count();
        $totalPages  = (int)ceil($totalCount / $limit);

        $pagination = [
            'page'    => $page,
            'pages'   => $totalPages,
            'limit'   => $limit,
            'count'   => $totalCount,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
        ];

        $statusOptions = [
            'pending'    => 'Pending',
            'confirmed'  => 'Confirmed',
            'processing' => 'Processing',
            'shipped'    => 'Shipped',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
        ];

        $this->set(compact('orders', 'statusOptions', 'status', 'dateFrom', 'dateTo', 'pagination'));
    }

    /** Order details */
    public function orderDetails($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Orders = $this->fetchTable('Orders');

        $order = $Orders->find()
            ->where(['Orders.id' => $id, 'Orders.user_id' => $userId])
            ->contain(['OrderItems' => ['Products']])
            ->first();

        if (!$order) {
            $this->Flash->error('Order not found.');
            return $this->redirect(['action' => 'orders']);
        }

        $this->set(compact('order'));
    }

    /** Profile */
    public function profile()
    {
        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Users     = $this->fetchTable('Users');
        $Addresses = $this->fetchTable('Addresses');

        $user = $Users->get($userId);
        $addresses = $Addresses->find()
            ->where(['user_id' => $userId])
            ->order(['is_default' => 'DESC', 'created' => 'ASC'])
            ->all();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $allowedFields = ['email'];

            $user = $Users->patchEntity($user, $data, ['fields' => $allowedFields]);

            if ($Users->save($user)) {
                $this->Flash->success('Profile updated successfully.');
                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('Unable to update profile.');
        }

        $this->set(compact('user', 'addresses'));
    }

    /** Settings — 读取/保存到 Cookie（本设备） */
    public function settings()
    {
        // 读取现有偏好（默认值与布局一致）
        $cookies = $this->request->getCookieParams();
        $prefs = [
            'theme'          => $cookies['pref_theme']      ?? 'auto',
            'contrast'       => $cookies['pref_contrast']   ?? 'normal',
            'font_scale'     => $cookies['pref_font_scale'] ?? '1.0',
            'language'       => $cookies['pref_language']   ?? 'en',
            'email_optin'    => !empty($cookies['email_optin']),
            'cookie_consent' => !empty($cookies['cookie_consent']),
        ];

        if ($this->request->is(['post', 'put', 'patch'])) {
            $d = (array)$this->request->getData();

            // 规范化输入
            $theme      = in_array(($d['theme'] ?? 'auto'), ['auto','light','dark'], true) ? $d['theme'] : 'auto';
            $contrast   = in_array(($d['contrast'] ?? 'normal'), ['normal','high'], true) ? $d['contrast'] : 'normal';
            $fontScale  = (string)max(0.9, min(1.25, (float)($d['font_scale'] ?? 1.0)));
            $language   = in_array(($d['language'] ?? 'en'), ['en','zh','ja'], true) ? $d['language'] : 'en';
            $emailOptin = !empty($d['email_optin']) ? '1' : '';
            $consent    = !empty($d['cookie_consent']) ? '1' : '';

            $expires = new \DateTimeImmutable('+1 year');

            $cookieDefs = [
                new Cookie('pref_theme',      $theme,     $expires, '/'),
                new Cookie('pref_contrast',   $contrast,  $expires, '/'),
                new Cookie('pref_font_scale', $fontScale, $expires, '/'),
                new Cookie('pref_language',   $language,  $expires, '/'),
                new Cookie('email_optin',     $emailOptin, $expires, '/'),
                new Cookie('cookie_consent',  $consent,    $expires, '/'),
            ];

            foreach ($cookieDefs as $ck) {
                $this->response = $this->response->withCookie($ck);
            }

            $this->Flash->success('Preferences saved to this device.');
            return $this->redirect(['action' => 'settings']);
        }

        $this->set(compact('prefs'));
    }

    /** Buy again -> cart */
    public function buyAgain($orderId = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Orders    = $this->fetchTable('Orders');
        $Carts     = $this->fetchTable('Carts');
        $CartItems = $this->fetchTable('CartItems');

        $order = $Orders->find()
            ->where(['Orders.id' => $orderId, 'Orders.user_id' => $userId])
            ->contain(['OrderItems'])
            ->first();

        if (!$order) {
            $this->Flash->error('Order not found.');
            return $this->redirect(['action' => 'orders']);
        }

        $cart = $Carts->find()
            ->where(['user_id' => $userId, 'status' => 'open'])
            ->first();

        if (!$cart) {
            $cart = $Carts->newEntity(['user_id' => $userId, 'status' => 'open', 'currency' => 'AUD']);
            $Carts->save($cart);
        }

        $addedCount = 0;
        foreach ($order->order_items as $orderItem) {
            if ($orderItem->product_id) {
                $existing = $CartItems->find()
                    ->where(['cart_id' => $cart->id, 'product_id' => $orderItem->product_id])
                    ->first();

                if ($existing) {
                    $existing->qty += $orderItem->qty;
                    $CartItems->save($existing);
                } else {
                    $ci = $CartItems->newEntity([
                        'cart_id'    => $cart->id,
                        'product_id' => $orderItem->product_id,
                        'qty'        => $orderItem->qty,
                        'price'      => $orderItem->price,
                        'currency'   => $orderItem->currency,
                    ]);
                    $CartItems->save($ci);
                }
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            $this->Flash->success(sprintf('%d items added to cart.', $addedCount));
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        }

        $this->Flash->error('No items could be added to cart.');
        return $this->redirect(['action' => 'orders']);
    }

    /** Add address */
    public function addAddress()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Addresses = $this->fetchTable('Addresses');
        $address   = $Addresses->newEmptyEntity();

        $data = $this->request->getData();
        $data['user_id'] = $userId;
        $data['type']    = 'billing';

        $address = $Addresses->patchEntity($address, $data);

        if ($Addresses->save($address)) {
            $this->Flash->success('Address added successfully.');
        } else {
            $this->Flash->error('Unable to add address. Please check the form.');
        }

        return $this->redirect(['action' => 'profile']);
    }

    /** Edit address */
    public function editAddress($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Addresses = $this->fetchTable('Addresses');
        $address   = $Addresses->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->first();

        if (!$address) {
            $this->Flash->error('Address not found.');
            return $this->redirect(['action' => 'profile']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data    = $this->request->getData();
            $address = $Addresses->patchEntity($address, $data);

            if ($Addresses->save($address)) {
                $this->Flash->success('Address updated successfully.');
                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('Unable to update address.');
        }

        $this->set(compact('address'));
    }

    /** Delete address */
    public function deleteAddress($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Addresses = $this->fetchTable('Addresses');
        $address   = $Addresses->find()
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

    /** Set default address */
    public function setDefaultAddress($id = null)
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Addresses = $this->fetchTable('Addresses');

        $Addresses->updateAll(['is_default' => false], ['user_id' => $userId]);

        $address = $Addresses->find()->where(['id' => $id, 'user_id' => $userId])->first();

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

    /** Logout */
    public function logout()
    {
        $this->Authentication->logout();
        $this->Flash->success('Signed out successfully.');
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }
}
