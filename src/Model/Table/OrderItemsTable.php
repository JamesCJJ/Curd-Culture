<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrderItemsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('order_items');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Orders', ['foreignKey' => 'order_id']);
        $this->belongsTo('Products', ['foreignKey' => 'product_id']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name')
            ->numeric('price')
            ->integer('qty')
            ->numeric('line_total');

        return $validator;
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $lineTotal = $entity->get('line_total');
        if ($lineTotal === null || $entity->isDirty('price') || $entity->isDirty('qty')) {
            $price = (float)($entity->get('price') ?? 0);
            $qty   = (int)($entity->get('qty') ?? 0);
            $entity->set('line_total', round($price * $qty, 2));
        }
    }
}
