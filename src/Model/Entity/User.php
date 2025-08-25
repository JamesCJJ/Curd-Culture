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
        'email'    => true,
        'password' => true,
        'role'     => true,
        'status'   => true,
        'created'  => true,
        'modified' => true,
    ];


    protected array $_hidden = ['password'];


    protected function _setPassword(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {

            return null;
        }
        return password_hash($trimmed, PASSWORD_DEFAULT);
    }
}
