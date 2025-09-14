<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\I18n\DateTime;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Text;

/**
 * Admin Products Controller
 * Comprehensive product management system
 */
class ProductsController extends AppController
{
    /**
     * Index method - List all products with search and filtering
     */
    public function index()
    {
        $query = trim((string)$this->request->getQuery('q'));
        $category = (string)$this->request->getQuery('category');
        $status = (string)$this->request->getQuery('status');
        
        $table = $this->fetchTable('Products');
        
        $productsQuery = $table->find()
            ->orderByDesc('Products.created');
            
        // Search functionality
        if ($query !== '') {
            $productsQuery->where([
                'OR' => [
                    'Products.name LIKE' => '%' . $query . '%',
                    'Products.description LIKE' => '%' . $query . '%',
                    'Products.summary LIKE' => '%' . $query . '%',
                    'Products.origin_country LIKE' => '%' . $query . '%',
                ]
            ]);
        }
        
        // Filter by stock status
        if ($status === 'in_stock') {
            $productsQuery->where(['Products.stock >' => 0]);
        } elseif ($status === 'out_of_stock') {
            $productsQuery->where(['Products.stock' => 0]);
        } elseif ($status === 'low_stock') {
            $productsQuery->where(['Products.stock <=' => 10, 'Products.stock >' => 0]);
        }
        
        // Pagination
        $limit = 20;
        $page = max(1, (int)$this->request->getQuery('page', 1));
        $offset = ($page - 1) * $limit;
        
        $products = $productsQuery->limit($limit)->offset($offset)->all();
        $totalCount = $productsQuery->count();
        $totalPages = (int)ceil($totalCount / $limit);
        
        // Statistics
        $stats = [
            'total' => $table->find()->count(),
            'in_stock' => $table->find()->where(['stock >' => 0])->count(),
            'out_of_stock' => $table->find()->where(['stock' => 0])->count(),
            'low_stock' => $table->find()->where(['stock <=' => 10, 'stock >' => 0])->count(),
        ];
        
        $pagination = [
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ];
        
        $this->set(compact('products', 'pagination', 'stats', 'query', 'status'));
    }
    
    /**
     * View method - Display single product details
     */
    public function view($id = null)
    {
        $product = $this->fetchTable('Products')->get($id);
        $this->set(compact('product'));
    }
    
    /**
     * Add method - Create new product
     */
    public function add()
    {
        $table = $this->fetchTable('Products');
        $product = $table->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data = $this->normalizeProductData($data);
            
            // Generate slug from name if not provided
            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Text::slug(strtolower($data['name']));
            }
            
            // Handle image upload
            if (!empty($data['image_file']) && $data['image_file']->getError() === UPLOAD_ERR_OK) {
                $imageUrl = $this->handleImageUpload($data['image_file']);
                if ($imageUrl) {
                    $data['image_url'] = $imageUrl;
                } else {
                    // Image upload failed, but don't prevent product creation
                    // Error message already set in handleImageUpload
                }
            } elseif (!empty($data['image_file']) && $data['image_file']->getError() !== UPLOAD_ERR_NO_FILE) {
                // Handle other upload errors
                $this->Flash->error(__('Image upload failed. Please try again.'));
            }
            
            $product = $table->patchEntity($product, $data);
            
            if ($table->save($product)) {
                $this->Flash->success(__('Product has been created successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            
            $this->Flash->error(__('Unable to create product. Please check the form and try again.'));
        }
        
        $this->set(compact('product'));
    }
    
    /**
     * Edit method - Update existing product
     */
    public function edit($id = null)
    {
        $table = $this->fetchTable('Products');
        $product = $table->get($id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data = $this->normalizeProductData($data);
            
            // Generate slug from name if not provided
            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Text::slug(strtolower($data['name']));
            }
            
            // Handle image upload
            if (!empty($data['image_file']) && $data['image_file']->getError() === UPLOAD_ERR_OK) {
                $imageUrl = $this->handleImageUpload($data['image_file']);
                if ($imageUrl) {
                    // Delete old image if exists
                    if ($product->image_url) {
                        $this->deleteImage($product->image_url);
                    }
                    $data['image_url'] = $imageUrl;
                } else {
                    // Image upload failed, but don't prevent product update
                    // Error message already set in handleImageUpload
                }
            } elseif (!empty($data['image_file']) && $data['image_file']->getError() !== UPLOAD_ERR_NO_FILE) {
                // Handle other upload errors
                $this->Flash->error(__('Image upload failed. Please try again.'));
            }
            
            $product = $table->patchEntity($product, $data);
            
            if ($table->save($product)) {
                $this->Flash->success(__('Product has been updated successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            
            $this->Flash->error(__('Unable to update product. Please check the form and try again.'));
        }
        
        $this->set(compact('product'));
    }
    
    /**
     * Delete method - Remove product
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable('Products');
        $product = $table->get($id);
        
        // Delete associated image
        if ($product->image_url) {
            $this->deleteImage($product->image_url);
        }
        
        if ($table->delete($product)) {
            $this->Flash->success(__('Product has been deleted successfully.'));
        } else {
            $this->Flash->error(__('Unable to delete product.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Bulk update stock levels
     */
    public function updateStock()
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();
        
        if (empty($data['products'])) {
            $this->Flash->error(__('No products selected.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $table = $this->fetchTable('Products');
        $updated = 0;
        
        foreach ($data['products'] as $productData) {
            if (!empty($productData['id']) && isset($productData['stock'])) {
                $product = $table->get($productData['id']);
                $product->stock = (int)$productData['stock'];
                if ($table->save($product)) {
                    $updated++;
                }
            }
        }
        
        $this->Flash->success(__('Updated stock levels for {0} products.', $updated));
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Export products to CSV
     */
    public function export()
    {
        $this->disableAutoRender();
        
        $products = $this->fetchTable('Products')->find()
            ->select([
                'id', 'name', 'slug', 'price', 'currency', 'summary', 
                'description', 'stock', 'origin_country', 'milk_type', 
                'age', 'style', 'vegetarian', 'gluten_free', 'lactose_free', 
                'created', 'modified'
            ])
            ->orderByDesc('created')
            ->all();
        
        $filename = 'products_' . DateTime::now()->format('Ymd_His') . '.csv';
        
        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename);
        
        $out = fopen('php://temp', 'r+');
        fputcsv($out, [
            'ID', 'Name', 'Slug', 'Price', 'Currency', 'Summary', 'Description',
            'Stock', 'Origin Country', 'Milk Type', 'Age', 'Style', 
            'Vegetarian', 'Gluten Free', 'Lactose Free', 'Created', 'Modified'
        ]);
        
        foreach ($products as $product) {
            fputcsv($out, [
                $product->id,
                $product->name,
                $product->slug,
                $product->price,
                $product->currency,
                $product->summary,
                $product->description,
                $product->stock,
                $product->origin_country,
                $product->milk_type,
                $product->age,
                $product->style,
                $product->vegetarian ? 'Yes' : 'No',
                $product->gluten_free ? 'Yes' : 'No',
                $product->lactose_free ? 'Yes' : 'No',
                $product->created?->format('Y-m-d H:i:s') ?? '',
                $product->modified?->format('Y-m-d H:i:s') ?? '',
            ]);
        }
        
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        
        return $this->response->withStringBody($csv);
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($uploadedFile)
    {
        $uploadPath = WWW_ROOT . 'img' . DS . 'products' . DS;
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedTypes)) {
            $supportedTypes = implode(', ', array_map('strtoupper', $allowedTypes));
            $attemptedType = strtoupper($extension ?: 'unknown');
            $this->Flash->error(__('Unsupported file type "{0}". Please upload an image file in one of these formats: {1}', [$attemptedType, $supportedTypes]));
            return false;
        }
        
        // Check file size (max 2MB to match PHP upload_max_filesize)
        if ($uploadedFile->getSize() > 2 * 1024 * 1024) {
            $this->Flash->error(__('Image file is too large. Maximum size is 2MB.'));
            return false;
        }
        
        $filename = uniqid('product_') . '.' . $extension;
        $destination = $uploadPath . $filename;
        
        try {
            $uploadedFile->moveTo($destination);
            return 'img/products/' . $filename;
        } catch (\Exception $e) {
            $this->Flash->error(__('Failed to upload image. Please try again.'));
            return false;
        }
    }
    
    /**
     * Delete image file
     */
    private function deleteImage($imageUrl)
    {
        $imagePath = WWW_ROOT . $imageUrl;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    /**
     * Normalize incoming product data to satisfy database constraints
     * - Convert empty enum values to null
     * - Coerce booleans to 0/1
     * - Cast numeric fields
     *
     * @param array $data
     * @return array
     */
    private function normalizeProductData(array $data): array
    {
        $normalized = $data;
        
        // pasteurised is ENUM('yes','no') NULLable
        $p = $normalized['pasteurised'] ?? null;
        if ($p === '' || $p === null) {
            $normalized['pasteurised'] = null;
        } else {
            $p = strtolower((string)$p);
            $normalized['pasteurised'] = in_array($p, ['yes', 'no'], true) ? $p : null;
        }

        // Booleans from checkboxes
        foreach (['vegetarian', 'gluten_free', 'lactose_free'] as $flag) {
            if (array_key_exists($flag, $normalized)) {
                $normalized[$flag] = (int)!empty($normalized[$flag]);
            }
        }

        // Numeric casts
        if (isset($normalized['price']) && $normalized['price'] !== '') {
            $normalized['price'] = (float)$normalized['price'];
        }
        if (isset($normalized['stock']) && $normalized['stock'] !== '') {
            $normalized['stock'] = (int)$normalized['stock'];
        }

        // Default currency
        if (empty($normalized['currency'])) {
            $normalized['currency'] = 'AUD';
        }

        return $normalized;
    }
}
