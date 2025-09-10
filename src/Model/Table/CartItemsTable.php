<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CartItems Model
 *
 * @property \App\Model\Table\CartsTable&\Cake\ORM\Association\BelongsTo $Carts
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\CartItem newEmptyEntity()
 * @method \App\Model\Entity\CartItem newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CartItem> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CartItem get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CartItem findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CartItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CartItem> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CartItem|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CartItem saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CartItem>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CartItem>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CartItem>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CartItem> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CartItem>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CartItem>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CartItem>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CartItem> deleteManyOrFail(iterable $entities, array $options = [])
 */
class CartItemsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('cart_items');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Carts', [
            'foreignKey' => 'cart_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('cart_id')
            ->notEmptyString('cart_id');

        $validator
            ->integer('product_id')
            ->notEmptyString('product_id');

        $validator
            ->integer('qty')
            ->requirePresence('qty', 'create')
            ->notEmptyString('qty')
            ->greaterThan('qty', 0);

        $validator
            ->decimal('price')
            ->requirePresence('price', 'create')
            ->notEmptyString('price');

        $validator
            ->scalar('currency')
            ->maxLength('currency', 3)
            ->allowEmptyString('currency');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['cart_id'], 'Carts'), ['errorField' => 'cart_id']);
        $rules->add($rules->existsIn(['product_id'], 'Products'), ['errorField' => 'product_id']);

        return $rules;
    }
}
