<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SiteSettingsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('site_settings');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('setting_key')
            ->maxLength('setting_key', 100)
            ->requirePresence('setting_key', 'create')
            ->notEmptyString('setting_key');

        $validator
            ->scalar('setting_type')
            ->maxLength('setting_type', 20)
            ->notEmptyString('setting_type');

        return $validator;
    }
}


