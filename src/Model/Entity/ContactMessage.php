<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ContactMessage extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'email' => true,
        'message' => true,
        'is_spam' => true,
        'created' => true,
        'modified' => true,
        // Virtual/non-persisted field for captcha input
        'captcha' => true,
    ];

    protected array $_hidden = [];
}
