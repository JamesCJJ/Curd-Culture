<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;

/**
 * CustomerController
 * - Adds a secure deleteAddress() action (POST/DELETE only, user-ownership check).
 * - Other actions kept as-is.
 */
class CustomerController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Flash');

    }

    /**
     * Enforce authentication and role gate for all actions here.
     */
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

    /** Orders list (with simple filters + pagination) */
    public function orders()
    {
        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

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
        if ($id === null) {
            $id = $this->request->getQuery('id');
        }

        if ($id === null || $id === '') {
            $this->Flash->error('Invalid order ID.');
            return $this->redirect(['action' => 'orders']);
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->Flash->error('Invalid order ID.');
            return $this->redirect(['action' => 'orders']);
        }

        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Orders = $this->fetchTable('Orders');

        $order = $Orders->find()
            ->where(['Orders.id' => $id, 'Orders.user_id' => $userId])
            ->contain([
                'OrderItems' => ['Products'],
                // NOTE: keep your custom selects here; ensure columns exist in DB.
                'DeliverySlots' => function ($q) {
                    return $q->select(['id','name','window_start','window_end']);
                },
                'PickupLocations' => function ($q) {
                    return $q->select(['id','name','address_line_1','suburb','state','postcode']);
                },
            ])
            ->first();

        if (!$order) {
            $this->Flash->error('Order not found.');
            return $this->redirect(['action' => 'orders']);
        }

        $this->set(compact('order'));
    }

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

        $defaultShippingId = null;
        $defaultBillingId  = null;
        foreach ($addresses as $addr) {
            if ($addr->is_default && ($addr->type ?? 'shipping') === 'shipping' && $defaultShippingId === null) {
                $defaultShippingId = (int)$addr->id;
            }
            if ($addr->is_default && ($addr->type ?? 'billing') === 'billing' && $defaultBillingId === null) {
                $defaultBillingId = (int)$addr->id;
            }
        }

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

        $this->set(compact('user', 'addresses', 'defaultShippingId', 'defaultBillingId'));
    }

    /** Device preferences demo */
    public function settings()
    {
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

    /**
     * Re-add the items from a past order to the current cart.
     */
    public function buyAgain($orderId = null)
    {
        $orderId = $orderId
            ?? (int)($this->request->getParam('pass.0') ?? 0)
            ?? 0;

        if (!$orderId) {
            $orderId = (int)($this->request->getQuery('id') ?? 0);
        }
        if (!$orderId) {
            $orderId = (int)($this->request->getData('id') ?? 0);
        }

        if ($orderId <= 0) {
            $this->Flash->error('Invalid order ID.');
            return $this->redirect(['action' => 'orders']);
        }

        $identity = $this->request->getAttribute('identity');
        $userId   = (int)$identity->get('id');

        $Orders    = $this->fetchTable('Orders');
        $Carts     = $this->fetchTable('Carts');
        $CartItems = $this->fetchTable('CartItems');

        $order = $Orders->find()
            ->where([
                'Orders.id'      => $orderId,
                'Orders.user_id' => $userId,
            ])
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

    /**
     * Add a new address for the current user.
     */
    public function addAddress()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $userId   = $identity->get('id');

        $Addresses = $this->fetchTable('Addresses');
        $address   = $Addresses->newEmptyEntity();

        $data = (array)$this->request->getData();

        $type = (string)($data['type'] ?? 'billing');
        if (!in_array($type, ['shipping','billing'], true)) {
            $type = 'billing';
        }

        $isDefault = !empty($data['is_default']) ? 1 : 0;

        // Enforce ownership and type
        $data['user_id']    = $userId;
        $data['type']       = $type;
        $data['is_default'] = $isDefault;

        $address = $Addresses->patchEntity($address, $data);

        // If marked as default, unset other defaults of the same type
        if ($isDefault === 1) {
            $Addresses->updateAll(
                ['is_default' => 0],
                ['user_id' => $userId, 'type' => $type]
            );
        }

        if ($Addresses->save($address)) {
            $this->Flash->success('Address added successfully.');
        } else {
            $this->Flash->error('Unable to add address. Please check the form.');
        }

        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Edit an existing address
     */
    public function editAddress($id = null)
    {
        $this->request->allowMethod(['patch','post','put']);

        $identity = $this->request->getAttribute('identity');
        $userId   = (int)$identity->get('id');

        $pass  = (array)($this->request->getParam('pass') ?? []);
        $rawId = $id
            ?? ($pass[0] ?? null)
            ?? $this->request->getData('id')
            ?? $this->request->getQuery('id');

        $addrId = is_numeric($rawId) ? (int)$rawId : 0;
        if ($addrId <= 0) {
            $this->Flash->error('Invalid address ID.');
            return $this->redirect(['action' => 'profile']);
        }

        $Addresses = $this->fetchTable('Addresses');

        $address = $Addresses->find()
            ->where(['id' => $addrId, 'user_id' => $userId])
            ->first();

        if (!$address) {
            $this->Flash->error('Address not found.');
            return $this->redirect(['action' => 'profile']);
        }

        $data = (array)$this->request->getData();

        $newType = (string)($data['type'] ?? $address->type ?? 'billing');
        if (!in_array($newType, ['shipping','billing'], true)) {
            $newType = 'billing';
        }
        $newDefault = !empty($data['is_default']) ? 1 : 0;

        if ($newDefault === 1) {
            $Addresses->updateAll(
                ['is_default' => 0],
                ['user_id' => $userId, 'type' => $newType]
            );
        }

        $data['user_id']    = $userId;
        $data['type']       = $newType;
        $data['is_default'] = $newDefault;

        $address = $Addresses->patchEntity($address, $data);

        if ($Addresses->save($address)) {
            $this->Flash->success('Address updated successfully.');
        } else {
            $this->Flash->error('Unable to update address.');
        }

        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Mark an address as default (scoped to its type).
     */
    public function setDefaultAddress($id = null)
    {
        $this->request->allowMethod(['post']);

        $pass = (array)($this->request->getParam('pass') ?? []);
        $rawId = $id
            ?? ($pass[0] ?? null)
            ?? $this->request->getData('id')
            ?? $this->request->getQuery('id');

        $addrId = is_numeric($rawId) ? (int)$rawId : 0;
        if ($addrId <= 0) {
            $this->Flash->error('Invalid address ID.');
            return $this->redirect(['action' => 'profile']);
        }

        $identity  = $this->request->getAttribute('identity');
        $userId    = (int)$identity->get('id');
        $Addresses = $this->fetchTable('Addresses');

        $exists = $Addresses->exists(['id' => $addrId, 'user_id' => $userId]);
        if (!$exists) {
            $this->Flash->error('Address not found.');
            return $this->redirect(['action' => 'profile']);
        }

        // You might have a custom Table method; call it if present.
        if (method_exists($Addresses, 'setDefaultForUser')) {
            $ok = $Addresses->setDefaultForUser($userId, $addrId);
        } else {
            // Fallback: mark given address default and unset others of same type.
            $addr = $Addresses->get($addrId);
            $type = $addr->type ?? 'billing';
            $Addresses->updateAll(['is_default' => 0], ['user_id' => $userId, 'type' => $type]);
            $addr->is_default = 1;
            $ok = (bool)$Addresses->save($addr);
        }

        if ($ok) {
            $this->Flash->success('Default address updated.');
        } else {
            $this->Flash->error('Unable to update default address.');
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

    /**
     * NEW: Securely delete an address that belongs to the logged-in user.
     * Route: POST/DELETE /dashboard/address/delete/:id
     */
    public function deleteAddress($id = null)
    {
        // Enforce HTTP method safety (prevents GET deletion)
        $this->request->allowMethod(['post','delete']);

        $pass  = (array)($this->request->getParam('pass') ?? []);
        $rawId = $id
            ?? ($pass[0] ?? null)
            ?? $this->request->getData('id')
            ?? $this->request->getQuery('id');

        $addrId = is_numeric($rawId) ? (int)$rawId : 0;
        if ($addrId <= 0) {
            throw new MethodNotAllowedException('Invalid address id.');
        }

        $identity  = $this->request->getAttribute('identity');
        $userId    = (int)$identity->get('id');
        $Addresses = $this->fetchTable('Addresses');

        // Only delete if the address belongs to the current user
        $address = $Addresses->find()
            ->where(['Addresses.id' => $addrId, 'Addresses.user_id' => $userId])
            ->first();

        if (!$address) {
            throw new RecordNotFoundException('Address not found or not owned by you.');
        }

        if ($Addresses->delete($address)) {
            $this->Flash->success('Address deleted.');
        } else {
            $this->Flash->error('Failed to delete address. Please try again.');
        }

        return $this->redirect(['action' => 'profile']);
    }
}
