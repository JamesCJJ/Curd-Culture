<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

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
        $this->Authentication->addUnauthenticatedActions(['index', 'view']);
    }

    // GET /products
    public function index()
    {
        $this->request->allowMethod(['get']);

        $limit  = max(1, min(48, (int)$this->request->getQuery('limit', 12)));
        $page   = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;

        $query = $this->Products->find()
            ->select([
                'id', 'name', 'slug', 'price', 'currency',
                'summary', 'image_url', 'rating', 'origin_country', 'milk_type', 'age'
            ])
            ->orderByDesc('created')
            ->limit($limit)
            ->offset($offset);

        $products = $query->all();

        $count = $this->Products->find()->count();
        $pages = (int)ceil($count / $limit);

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

    // GET /products/:key
    public function view(string $key)
    {
        $query = ctype_digit($key)
            ? $this->Products->find()->where(['id' => (int)$key])
            : $this->Products->find()->where(['slug' => $key]);

        $product = $query->first();
        if (!$product) {
            throw new NotFoundException('Product not found.');
        }


        if ($this->request->is('ajax') || $this->request->getQuery('modal')) {
            $this->viewBuilder()->setLayout('ajax');
            $this->set(compact('product'));
            return $this->render('view_modal');
        }


        $this->set(compact('product'));

    }

    // POST /products/add-to-cart/:id
    public function addToCart(int $id)
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $role     = strtolower((string)($identity?->get('role') ?? ''));

        if (!$identity || $role !== 'customer') {
            $this->Flash->error('Please sign in as a customer to add items to your cart.');
            return $this->redirect([
                'controller' => 'Users',
                'action' => 'login',
                '?' => ['redirect' => $this->request->getRequestTarget()],
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

        $session = $this->request->getSession();
        $cart = (array)$session->read('Cart.items');

        $cart[$id] = [
            'id'       => $product->id,
            'name'     => (string)$product->name,
            'slug'     => (string)$product->slug,
            'price'    => (float)$product->price,
            'currency' => (string)($product->currency ?: 'AUD'),
            'qty'      => (int)(($cart[$id]['qty'] ?? 0) + $qty),
        ];

        $session->write('Cart.items', $cart);

        $this->Flash->success('Added to your cart.');
        return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
    }
}
