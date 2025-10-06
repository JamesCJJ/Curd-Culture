<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use ArrayObject;

/**
 * Products table
 * - Adds decrementStockOrFail() with transaction + row-level lock
 */
class ProductsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setPrimaryKey('id');
        $this->setDisplayField('name');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $v): Validator
    {
        $v->scalar('name')->maxLength('name', 200)->notEmptyString('name');
        $v->scalar('slug')->maxLength('slug', 200)->allowEmptyString('slug');
        $v->numeric('price')->greaterThanOrEqual('price', 0)->allowEmptyString('price');
        $v->scalar('currency')->maxLength('currency', 3)->allowEmptyString('currency');
        $v->allowEmptyString('summary');
        $v->allowEmptyString('description');
        $v->allowEmptyString('image_url');

        $v->integer('stock')->greaterThanOrEqual('stock', 0)->allowEmptyString('stock');
        $v->numeric('rating')->range('rating', [0, 5])->allowEmptyString('rating');

        return $v;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['slug'], 'Slug already exists.'), ['errorField' => 'slug']);
        return $rules;
    }

    public function beforeSave(EventInterface $event, $entity, ArrayObject $options)
    {
        if (empty($entity->slug) && !empty($entity->name)) {
            $entity->slug = strtolower((string)Text::slug((string)$entity->name));
        }
    }

    /**
     * Atomically decrement stock with SELECT ... FOR UPDATE and a single transaction.
     * - If stock is NULL, treat as "unlimited" and do nothing.
     * - Throws RuntimeException for missing product or insufficient stock.
     */
    public function decrementStockOrFail(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        $conn = $this->getConnection();
        $conn->begin();

        try {
            // Row-level lock
            $product = $this->find()
                ->where(['id' => $productId])
                ->applyOptions(['forUpdate' => true])
                ->first();

            if (!$product) {
                throw new \RuntimeException('Product not found: ' . $productId);
            }

            if ($product->stock === null) {
                $conn->commit();
                return; // unlimited
            }

            $current = (int)$product->stock;
            if ($current < $qty) {
                $name = (string)($product->name ?? ('#' . $productId));
                throw new \RuntimeException('Insufficient stock for: ' . $name);
            }

            $product->stock = $current - $qty;
            $this->saveOrFail($product, ['atomic' => false]);

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }
}
