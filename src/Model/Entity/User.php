<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'email' => true,
        'password' => true,
        'role' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = ['password'];

    protected function _setPassword(?string $password): ?string
    {
        if (strlen((string)$password) === 0) {
            return null;
        }
        return (new DefaultPasswordHasher())->hash($password);
    }
}
