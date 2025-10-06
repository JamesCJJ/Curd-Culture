<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class DeliverySlotsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('delivery_slots');
        $this->setPrimaryKey('id');


        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $v): Validator
    {
        $v->scalar('name')->maxLength('name', 120)->notEmptyString('name');
        $v->allowEmptyTime('window_start');
        $v->allowEmptyTime('window_end');
        $v->integer('capacity')->allowEmptyString('capacity');
        $v->boolean('is_active')->allowEmptyString('is_active');
        return $v;
    }
}
