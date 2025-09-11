<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class ProductsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        // Public pages
        $this->Authentication->addUnauthenticatedActions(['index', 'view']);
    }

    public function index()
    {
        $this->request->allowMethod(['get']);

        $limit  = max(1, min(48, (int)$this->request->getQuery('limit', 12)));
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $query = $this->Products->find()
            ->select([
                'id','name','slug','price','currency','summary','image_url',
                'rating','origin_country','milk_type','age','created'
            ])
            ->orderByDesc('created')
            ->limit($limit)
            ->offset($offset);

        $products = $query->all();
        $count    = $this->Products->find()->count();
        $pages    = (int)ceil($count / $limit);

        $paging = [
            'count'   => $count,
            'page'    => $page,
            'pages'   => $pages,
            'limit'   => $limit,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $pages,
        ];

        $this->set(compact('products', 'paging'));
    }

    /**
     * /products/view/{slug-or-id}?modal=1
     */
    public function view(string $key = null)
    {
        if ($key === null || $key === '') {
            throw new NotFoundException('Product not found.');
        }

        // Find by slug or numeric id
        $Products = $this->fetchTable('Products');
        $product = $Products->find()
            ->where([
                'OR' => array_filter([
                    ['Products.slug' => $key],
                    ctype_digit($key) ? ['Products.id' => (int)$key] : null,
                ]),
            ])
            ->first();

        if (!$product) {
            throw new NotFoundException('Product not found.');
        }

        // Purchase permission for view/view_modal
        $canPurchase = true;
        $identity    = $this->request->getAttribute('identity');
        if ($identity && strtolower((string)$identity->get('role')) === 'admin') {
            $canPurchase = false;
        }

        $this->set(compact('product', 'canPurchase'));

        // Modal rendering (AJAX or ?modal=1)
        if ($this->request->is('ajax') || $this->request->getQuery('modal') === '1') {
            $this->viewBuilder()->disableAutoLayout();
            return $this->render('view_modal');
        }

        // Full page render
        return $this->render('view');
    }

    public function addToCart(int $id)
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $role     = strtolower((string)($identity?->get('role') ?? ''));

        if (!$identity || $role !== 'customer') {
            $this->Flash->error('Please sign in as a customer to add items to your cart.');
            return $this->redirect([
                'controller' => 'Users',
                'action'     => 'login',
                '?'          => ['redirect' => $this->request->getRequestTarget()],
            ]);
        }

        $product = $this->Products->find()
            ->select(['id','name','slug','price','currency'])
            ->where(['id' => $id])
            ->first();

        if (!$product) {
            $this->Flash->error('Product not found.');
            return $this->redirect(['action' => 'index']);
        }

        $qty = max(1, (int)$this->request->getData('qty'));

        $locator   = TableRegistry::getTableLocator();
        $Carts     = $locator->get('Carts');
        $CartItems = $locator->get('CartItems');

        $cart = $Carts->find()
            ->where(['user_id' => (int)$identity->get('id'), 'status' => 'open'])
            ->first();

        if (!$cart) {
            $cart = $Carts->newEntity([
                'user_id'  => (int)$identity->get('id'),
                'status'   => 'open',
                'currency' => (string)($product->currency ?: 'AUD'),
            ]);
            $Carts->saveOrFail($cart);
        }

        $item = $CartItems->find()
            ->where(['cart_id' => $cart->id, 'product_id' => $product->id])
            ->first();

        if ($item) {
            $item->qty = (int)$item->qty + $qty;
        } else {
            $item = $CartItems->newEntity([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'qty'        => $qty,
                'price'      => (float)$product->price,
                'currency'   => (string)($product->currency ?: 'AUD'),
            ]);
        }
        $CartItems->saveOrFail($item);

        $this->Flash->success('Added to your cart.');
        return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
    }
}
