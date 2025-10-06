<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class PickupLocation extends Entity
{

    protected array $_accessible = [
        'name'            => true,
        'address_line_1'  => true,
        'address_line_2'  => true,
        'suburb'          => true,
        'state'           => true,
        'postcode'        => true,
        'open_from'       => true,
        'open_to'         => true,
        'is_active'       => true,
        'created'         => true,
        'modified'        => true,

    ];
}
