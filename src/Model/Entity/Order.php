<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Order Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string $full_name
 * @property string $address
 * @property string $city
 * @property string $postcode
 * @property string $country
 * @property string $currency
 * @property float $subtotal
 * @property float $shipping_fee
 * @property float $discount
 * @property float $total
 * @property string $status
 * @property string $payment_status
 * @property string|null $payment_method
 * @property string|null $payment_ref
 * @property \Cake\I18n\DateTime|null $paid_at
 * @property string|null $notes
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\OrderItem[] $order_items
 */
class Order extends Entity
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
        'user_id' => true,
        'email' => true,
        'full_name' => true,
        'address' => true,
        'city' => true,
        'postcode' => true,
        'country' => true,
        'currency' => true,
        'subtotal' => true,
        'shipping_fee' => true,
        'discount' => true,
        'total' => true,
        'status' => true,
        'payment_status' => true,
        'payment_method' => true,
        'payment_ref' => true,
        'paid_at' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'order_items' => true,
    ];
}
