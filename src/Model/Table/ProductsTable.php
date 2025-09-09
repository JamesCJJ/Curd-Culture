<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use ArrayObject;

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
}
