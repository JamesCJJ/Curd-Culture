<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User entity
 */
class User extends Entity
{

    protected array $_accessible = [
        'name' => true,
        'email'    => true,
        'password' => true,
        'role'     => true,
        'status'   => true,
        'created'  => true,
        'modified' => true,
    ];


    protected array $_hidden = ['password'];


    use Cake\Auth\DefaultPasswordHasher;

    protected function _setPassword(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (password_get_info($value)['algo'] !== 0) {
            return $value;
        }
        return (new DefaultPasswordHasher())->hash($value);
    }

}
