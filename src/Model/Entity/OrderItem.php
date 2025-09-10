<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrderItem Entity
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $name
 * @property string|null $slug
 * @property float $price
 * @property string $currency
 * @property int $qty
 * @property float $line_total
 * @property string|null $snapshot
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Order $order
 * @property \App\Model\Entity\Product $product
 */
class OrderItem extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'order_id' => true,
        'product_id' => true,
        'name' => true,
        'slug' => true,
        'price' => true,
        'currency' => true,
        'qty' => true,
        'line_total' => true,
        'snapshot' => true,
        'created' => true,
        'modified' => true,
        'order' => true,
        'product' => true,
    ];
}
