<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

class CartController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    protected function getOpenCart(int $userId)
    {
        $locator = TableRegistry::getTableLocator();
        $Carts   = $locator->get('Carts');

        $cart = $Carts->find()->where(['user_id' => $userId, 'status' => 'open'])->first();
        if (!$cart) {
            $cart = $Carts->newEntity(['user_id' => $userId, 'status' => 'open', 'currency' => 'AUD']);
            $Carts->saveOrFail($cart);
        }
        return $cart;
    }

    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $userId   = (int)($identity?->get('id') ?? 0);

        $items    = [];
        $currency = 'AUD';
        $subtotal = 0.0;

        if ($userId) {
            $locator   = TableRegistry::getTableLocator();
            $CartItems = $locator->get('CartItems');
            $Products  = $locator->get('Products');

            $cart = $this->getOpenCart($userId);

            $rows = $CartItems->find()
                ->select(['id','product_id','qty','price','currency'])
                ->where(['cart_id' => $cart->id])
                ->all();

            foreach ($rows as $row) {
                $p = $Products->find()->select(['id','name','slug'])->where(['id' => $row->product_id])->first();
                if (!$p) { continue; }
                $items[$row->product_id] = [
                    'product_id' => (int)$row->product_id,
                    'name'       => (string)$p->name,
                    'slug'       => (string)$p->slug,
                    'price'      => (float)$row->price,
                    'currency'   => (string)($row->currency ?: 'AUD'),
                    'qty'        => (int)$row->qty,
                ];
                $subtotal += ((float)$row->price) * ((int)$row->qty);
                $currency = (string)($row->currency ?: $currency);
            }
        }

        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;

        $this->set(compact('items', 'currency', 'subtotal', 'shipping', 'total'));
    }

    public function update()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->redirect(['action' => 'index']);
        }

        $qtys = (array)$this->request->getData('qty');
        $locator   = TableRegistry::getTableLocator();
        $CartItems = $locator->get('CartItems');

        $cart = $this->getOpenCart((int)$identity->get('id'));

        foreach ($qtys as $productId => $qty) {
            $productId = (int)$productId;
            $qty       = max(0, (int)$qty);
            $item = $CartItems->find()
                ->where(['cart_id' => $cart->id, 'product_id' => $productId])
                ->first();
            if (!$item) { continue; }
            if ($qty === 0) {
                $CartItems->delete($item);
            } else {
                $item->qty = $qty;
                $CartItems->save($item);
            }
        }

        $this->Flash->success('Cart updated.');
        return $this->redirect(['action' => 'index']);
    }

    public function remove(int $productId)
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            return $this->redirect(['action' => 'index']);
        }

        $locator   = TableRegistry::getTableLocator();
        $CartItems = $locator->get('CartItems');

        $cart = $this->getOpenCart((int)$identity->get('id'));
        $item = $CartItems->find()->where(['cart_id' => $cart->id, 'product_id' => $productId])->first();
        if ($item) {
            $CartItems->delete($item);
        }

        $this->Flash->success('Item removed.');
        return $this->redirect(['action' => 'index']);
    }

    public function checkout()
    {
        $identity = $this->request->getAttribute('identity');
        $role     = strtolower((string)($identity?->get('role') ?? ''));

        if (!$identity || $role !== 'customer') {
            $this->Flash->error('Please sign in as a customer to checkout.');
            return $this->redirect([
                'controller' => 'Users',
                'action' => 'login',
                '?' => ['redirect' => $this->request->getRequestTarget()]
            ]);
        }

        $userId  = (int)$identity->get('id');
        $locator = TableRegistry::getTableLocator();
        $CartItems = $locator->get('CartItems');
        $Products  = $locator->get('Products');

        $cart = $this->getOpenCart($userId);
        $rows = $CartItems->find()->where(['cart_id' => $cart->id])->all();
        if ($rows->isEmpty()) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }

        if ($this->request->is('post')) {
            $data = (array)$this->request->getData();
            foreach (['full_name','email','address','city','postcode','country'] as $f) {
                if (empty(trim((string)($data[$f] ?? '')))) {
                    $this->Flash->error('Please fill all required fields.');
                    return;
                }
            }

            $Orders      = $locator->get('Orders');
            $OrderItems  = $locator->get('OrderItems');
            $ContactMsgs = $locator->get('ContactMessages');

            $subtotal = 0.0; $currency = 'AUD';
            foreach ($rows as $r) { $subtotal += (float)$r->price * (int)$r->qty; $currency = $r->currency ?: $currency; }
            $shipping = 12.90;
            $total    = $subtotal + $shipping;

            $order = $Orders->newEntity([
                'user_id'    => $userId,
                'status'     => 'paid',
                'currency'   => $currency,
                'subtotal'   => $subtotal,
                'shipping'   => $shipping,
                'total'      => $total,
                'full_name'  => $data['full_name'],
                'email'      => $data['email'],
                'address'    => $data['address'],
                'city'       => $data['city'],
                'postcode'   => $data['postcode'],
                'country'    => $data['country'],
            ]);
            $Orders->saveOrFail($order);

            foreach ($rows as $r) {
                $p = $Products->find()->select(['id','name','slug'])->where(['id' => $r->product_id])->first();
                if (!$p) { continue; }
                $OrderItems->saveOrFail($OrderItems->newEntity([
                    'order_id'   => $order->id,
                    'product_id' => (int)$p->id,
                    'name'       => (string)$p->name,
                    'slug'       => (string)$p->slug,
                    'qty'        => (int)$r->qty,
                    'price'      => (float)$r->price,
                    'currency'   => (string)($r->currency ?: $currency),
                ]));
            }

            $locator->get('Carts')->updateAll(['status' => 'ordered'], ['id' => $cart->id]);
            $CartItems->deleteAll(['cart_id' => $cart->id]);

            $ContactMsgs->save($ContactMsgs->newEntity([
                'name'    => (string)$data['full_name'],
                'email'   => (string)$data['email'],
                'message' => 'New order #'.$order->id.' placed. Total: '.$currency.' '.$total,
            ]));

            $this->Flash->success('Order placed! Thank you for your purchase.');
            return $this->redirect(['action' => 'complete']);
        }

        $prefill = [
            'full_name' => (string)($identity->get('name') ?? ''),
            'email'     => (string)($identity->get('email') ?? ''),
        ];
        $this->set(compact('prefill'));
    }

    public function complete()
    {
        $this->request->allowMethod(['get']);
    }
}
