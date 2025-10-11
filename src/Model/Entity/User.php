<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{

    protected array $_accessible = [
        'name'     => true,
        'username' => true,
        'email'    => true,
        'password' => true,
        'role'     => true,
        'status'   => true,
        'timezone' => true,
        'language' => true,
        'theme'    => true,
        'notify_email' => true,
        'notify_push'  => true,
        'created'  => true,
        'modified' => true,
        'pref_theme' => true,
        'pref_contrast' => true,
        'pref_font_scale' => true,
        'pref_lang' => true,
        'email_optin' => true,
        'cookie_consent' => true,
    ];


    protected array $_hidden = [
        'password',
        'reset_code_hash',
        'reset_expires',
        'reset_attempts',
    ];
    /**
     * Hash password on assignment.
     * - null: keep as-is
     * - "": return null
     */
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
