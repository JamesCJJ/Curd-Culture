<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Address Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $first_name
 * @property string $last_name
 * @property string|null $company
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $suburb
 * @property string $state
 * @property string $postcode
 * @property string $country
 * @property string|null $phone
 * @property bool $is_default
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Address extends Entity
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
        'type' => true,
        'first_name' => true,
        'last_name' => true,
        'company' => true,
        'address_line_1' => true,
        'address_line_2' => true,
        'suburb' => true,
        'state' => true,
        'postcode' => true,
        'country' => true,
        'phone' => true,
        'is_default' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    /**
     * Get the full name
     *
     * @return string
     */
    protected function _getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the formatted address
     *
     * @return string
     */
    protected function _getFormattedAddress(): string
    {
        $address = $this->address_line_1;
        if (!empty($this->address_line_2)) {
            $address .= ', ' . $this->address_line_2;
        }
        $address .= ', ' . $this->suburb . ' ' . $this->state . ' ' . $this->postcode;
        $address .= ', ' . $this->country;
        
        return $address;
    }
}
