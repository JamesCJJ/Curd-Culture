<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

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


    private function findOpenCart(int $userId)
    {
        $Carts = $this->getTableLocator()->get('Carts');
        return $Carts->find()
            ->where(['user_id' => $userId, 'status' => 'open'])
            ->first();
    }

    /** GET /cart */
    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? (int)$identity->get('id') : 0;

        $items = [];
        $currency = 'AUD';
        $subtotal = 0.0;

        if ($userId) {
            $cart = $this->findOpenCart($userId);

            if ($cart) {
                $CartItems = $this->getTableLocator()->get('CartItems');
                $Products  = $this->getTableLocator()->get('Products');

                $rows = $CartItems->find()
                    ->select(['id', 'product_id', 'qty', 'price', 'currency'])
                    ->where(['cart_id' => $cart->id])
                    ->enableHydration(false)
                    ->all()
                    ->toArray();

                if ($rows) {
                    $pids  = array_column($rows, 'product_id');
                    $map   = [];
                    $pRows = $Products->find()
                        ->select(['id', 'name', 'slug'])
                        ->where(['id IN' => $pids])
                        ->enableHydration(false)
                        ->all();

                    foreach ($pRows as $p) {
                        $map[(int)$p['id']] = $p;
                    }

                    foreach ($rows as $r) {
                        $pid  = (int)$r['product_id'];
                        $curr = $r['currency'] ?: 'AUD';

                        $item = [
                            'id'        => (int)$r['id'],
                            'product_id'=> $pid,
                            'name'      => $map[$pid]['name'] ?? ('#' . $pid),
                            'slug'      => $map[$pid]['slug'] ?? '',
                            'price'     => (float)$r['price'],
                            'currency'  => $curr,
                            'qty'       => (int)$r['qty'],
                        ];
                        $items[$item['id']] = $item;
                        $currency = $curr ?: $currency;
                        $subtotal += $item['price'] * $item['qty'];
                    }
                }
            }
        }

        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;

        $this->set(compact('items', 'currency', 'subtotal', 'shipping', 'total'));
    }

    /** POST /cart/update */
    public function update()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please sign in to update your cart.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = (int)$identity->get('id');
        $cart = $this->findOpenCart($userId);
        if (!$cart) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['action' => 'index']);
        }

        $qtys = (array)$this->request->getData('qty'); // ['cart_item_id' => qty]
        $CartItems = $this->getTableLocator()->get('CartItems');

        foreach ($qtys as $itemId => $qty) {
            $itemId = (int)$itemId;
            $qty    = max(0, (int)$qty);

            if ($qty === 0) {
                $CartItems->deleteAll(['id' => $itemId, 'cart_id' => $cart->id]);
            } else {
                $CartItems->updateAll(
                    ['qty' => $qty],
                    ['id' => $itemId, 'cart_id' => $cart->id]
                );
            }
        }

        $this->Flash->success('Cart updated.');
        return $this->redirect(['action' => 'index']);
    }

    /** POST /cart/remove/:id */
    public function remove(int $id)
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please sign in to modify your cart.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = (int)$identity->get('id');
        $cart = $this->findOpenCart($userId);
        if ($cart) {
            $CartItems = $this->getTableLocator()->get('CartItems');
            $CartItems->deleteAll(['id' => (int)$id, 'cart_id' => $cart->id]);
        }

        if ($this->request->is('ajax')) {
            return $this->response->withStatus(204);
        }

        $this->Flash->success('Item removed.');
        return $this->redirect(['action' => 'index']);
    }


    public function checkout()
    {
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please sign in to checkout.');
            return $this->redirect([
                'controller' => 'Users',
                'action'     => 'login',
                '?'          => ['redirect' => $this->request->getRequestTarget()],
            ]);
        }

        $userId = (int)$identity->get('id');
        $cart   = $this->findOpenCart($userId);
        if (!$cart) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }


        $CartItems = $this->getTableLocator()->get('CartItems');
        $Products  = $this->getTableLocator()->get('Products');

        $rows = $CartItems->find()
            ->select(['id', 'product_id', 'qty', 'price', 'currency'])
            ->where(['cart_id' => $cart->id])
            ->enableHydration(false)
            ->all()
            ->toArray();

        $items = [];
        $currency = 'AUD';
        $subtotal = 0.0;

        if ($rows) {
            $pids  = array_column($rows, 'product_id');
            $map   = [];
            $pRows = $Products->find()
                ->select(['id', 'name', 'slug'])
                ->where(['id IN' => $pids])
                ->enableHydration(false)
                ->all();

            foreach ($pRows as $p) {
                $map[(int)$p['id']] = $p;
            }

            foreach ($rows as $r) {
                $pid  = (int)$r['product_id'];
                $curr = $r['currency'] ?: 'AUD';
                $item = [
                    'id'         => (int)$r['id'],
                    'product_id' => $pid,
                    'name'       => $map[$pid]['name'] ?? ('#' . $pid),
                    'slug'       => $map[$pid]['slug'] ?? '',
                    'price'      => (float)$r['price'],
                    'currency'   => $curr,
                    'qty'        => (int)$r['qty'],
                ];
                $items[]  = $item;
                $currency = $curr ?: $currency;
                $subtotal += $item['price'] * $item['qty'];
            }
        }

        if (empty($items)) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }

        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;


        if ($this->request->is('post')) {
            $data = (array)$this->request->getData();

            $required = ['full_name','email','address','city','postcode','country'];
            foreach ($required as $f) {
                if (empty(trim((string)($data[$f] ?? '')))) {
                    $this->Flash->error('Please fill all required fields.');

                    $prefill = [
                        'full_name' => (string)($identity->get('name')  ?? ''),
                        'email'     => (string)($identity->get('email') ?? ''),
                    ];
                    $this->set(compact('items','currency','subtotal','shipping','total','prefill'));
                    return;
                }
            }

            $Orders      = $this->getTableLocator()->get('Orders');
            $OrderItems  = $this->getTableLocator()->get('OrderItems');

            $order = $Orders->newEntity([
                'user_id'        => $userId,
                'email'          => (string)$data['email'],
                'full_name'      => (string)$data['full_name'],
                'address'        => (string)$data['address'],
                'city'           => (string)$data['city'],
                'postcode'       => (string)$data['postcode'],
                'country'        => (string)$data['country'],
                'currency'       => $currency,
                'subtotal'       => $subtotal,
                'shipping_fee'   => $shipping,
                'discount'       => 0,
                'total'          => $total,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
            ]);

            $Orders->saveOrFail($order);

            foreach ($items as $it) {
                $lineTotal = (float)round(((float)$it['price']) * ((int)$it['qty']), 2);
                $OrderItems->saveOrFail($OrderItems->newEntity([
                    'order_id'   => $order->id,
                    'product_id' => $it['product_id'],
                    'name'       => $it['name'],
                    'slug'       => $it['slug'],
                    'qty'        => $it['qty'],
                    'price'      => $it['price'],
                    'currency'   => $it['currency'],
                    'line_total' => $lineTotal,
                ]));
            }


            $Carts = $this->getTableLocator()->get('Carts');
            $Carts->updateAll(['status' => 'ordered'], ['id' => $cart->id]);
            $CartItems->deleteAll(['cart_id' => $cart->id]);


            try {
                $ContactMsgs = $this->getTableLocator()->get('ContactMessages');
                $ContactMsgs->save($ContactMsgs->newEntity([
                    'name'    => (string)$data['full_name'],
                    'email'   => (string)$data['email'],
                    'message' => 'New order #' . $order->id . ' placed. Total: ' . $currency . ' ' . number_format($total, 2),
                ]));
            } catch (\Throwable $e) {

            }

            $this->Flash->success('Order placed! Thank you for your purchase.');
            return $this->redirect(['action' => 'complete']);
        }


        $prefill = [
            'full_name' => (string)($identity->get('name')  ?? ''),
            'email'     => (string)($identity->get('email') ?? ''),
        ];
        $this->set(compact('items','currency','subtotal','shipping','total','prefill'));
    }

    /** GET /checkout/complete */
    public function complete()
    {
        $this->request->allowMethod(['get']);
    }
}
