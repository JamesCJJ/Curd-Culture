<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PickupLocationsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('pickup_locations');
        $this->setPrimaryKey('id');
        $this->setDisplayField('name');


        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created'  => 'new',
                    'modified' => 'always',
                ],
            ],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        // 基本字段
        $validator
            ->scalar('name')
            ->maxLength('name', 120)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('address_line_1')
            ->maxLength('address_line_1', 255)
            ->requirePresence('address_line_1', 'create')
            ->notEmptyString('address_line_1');

        $validator
            ->scalar('address_line_2')
            ->maxLength('address_line_2', 255)
            ->allowEmptyString('address_line_2');

        $validator
            ->scalar('suburb')
            ->maxLength('suburb', 100)
            ->requirePresence('suburb', 'create')
            ->notEmptyString('suburb');

        $validator
            ->scalar('state')
            ->maxLength('state', 50)
            ->requirePresence('state', 'create')
            ->notEmptyString('state');

        $validator
            ->scalar('postcode')
            ->maxLength('postcode', 10)
            ->requirePresence('postcode', 'create')
            ->notEmptyString('postcode');


        $validator->allowEmptyTime('open_from');
        $validator->allowEmptyTime('open_to');


        $validator->add('open_to', 'pairedWithFrom', [
            'rule' => function ($value, array $context): bool {
                $from = $context['data']['open_from'] ?? null;
                $hasFrom = !($from === null || $from === '');
                $hasTo   = !($value === null || $value === '');

                return ($hasFrom && $hasTo) || (!$hasFrom && !$hasTo);
            },
            'message' => 'Please provide both "Open From" and "Open To", or leave both blank.',
        ]);

        $validator->add('open_from', 'pairedWithTo', [
            'rule' => function ($value, array $context): bool {
                $to = $context['data']['open_to'] ?? null;
                $hasFrom = !($value === null || $value === '');
                $hasTo   = !($to === null || $to === '');
                return ($hasFrom && $hasTo) || (!$hasFrom && !$hasTo);
            },
            'message' => 'Please provide both "Open From" and "Open To", or leave both blank.',
        ]);


        $validator->add('open_to', 'timeOrder', [
            'rule' => function ($to, array $context): bool {
                $from = $context['data']['open_from'] ?? null;


                if ($from === null || $from === '' || $to === null || $to === '') {
                    return true;
                }


                $toSec   = self::toSeconds($to);
                $fromSec = self::toSeconds($from);
                if ($toSec === null || $fromSec === null) {
                    return false;
                }

                return $fromSec < $toSec;
            },
            'message' => '"Open To" must be later than "Open From" (same day).',
        ]);


        $validator->boolean('is_active')->allowEmptyString('is_active');

        return $validator;
    }


    private static function toSeconds(mixed $t): ?int
    {
        if ($t instanceof \DateTimeInterface) {
            return ((int)$t->format('H')) * 3600 + ((int)$t->format('i')) * 60 + (int)$t->format('s');
        }
        if (is_string($t)) {
            $t = trim($t);
            if ($t === '') {
                return null;
            }
            $dt = \DateTimeImmutable::createFromFormat('H:i:s', $t)
                ?: \DateTimeImmutable::createFromFormat('H:i', $t)
                    ?: @new \DateTimeImmutable($t);
            if ($dt === false) {
                return null;
            }
            return ((int)$dt->format('H')) * 3600 + ((int)$dt->format('i')) * 60 + (int)$dt->format('s');
        }
        return null;
    }
}
