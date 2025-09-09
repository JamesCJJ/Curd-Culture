<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Product extends Entity
{
    protected array $_accessible = [
        'name'            => true,
        'slug'            => true,
        'summary'         => true,
        'description'     => true,
        'price'           => true,
        'currency'        => true,
        'image_url'       => true,
        'gallery'         => true, // json or text
        'stock'           => true,
        'rating'          => true,
        'origin_country'  => true,
        'milk_type'       => true,  // Cow/Goat/Sheep/Buffalo
        'age'             => true,  // e.g., "12 months"
        'style'           => true,  // e.g., "Washed rind", "Blue"
        'rennet'          => true,  // Animal/Vegetarian
        'pasteurised'     => true,  // yes/no
        'fat_content'     => true,  // %
        'allergens'       => true,  // e.g., "Milk"
        'vegetarian'      => true,  // bool-ish
        'gluten_free'     => true,  // bool-ish
        'lactose_free'    => true,  // bool-ish
        'pairing_notes'   => true,
        'awards'          => true,
        'created'         => true,
        'modified'        => true,
    ];
}
