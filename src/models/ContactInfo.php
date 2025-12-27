<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ContactInfo Model
 * 
 * Represents contact information (address, phone, email, social links).
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $type
 * @property string|null $label
 * @property string $value
 * @property string|null $icon
 * @property string|null $link
 * @property int $display_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ContactInfo extends Model
{
    protected $table = 'contact_info';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const TYPE_ADDRESS = 'address';
    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';
    const TYPE_SOCIAL = 'social';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'created_by',
        'updated_by',
        'type',
        'label',
        'value',
        'icon',
        'link',
        'display_order',
        'status',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function createdBy()
    {
        return $this->belongsTo(WebAdmin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(WebAdmin::class, 'updated_by');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)->orderBy('display_order');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAddresses($query)
    {
        return $query->where('type', self::TYPE_ADDRESS);
    }

    public function scopePhones($query)
    {
        return $query->where('type', self::TYPE_PHONE);
    }

    public function scopeEmails($query)
    {
        return $query->where('type', self::TYPE_EMAIL);
    }

    public function scopeSocials($query)
    {
        return $query->where('type', self::TYPE_SOCIAL);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function getActiveContacts()
    {
        return static::active()->get();
    }

    public static function getActiveByType(string $type)
    {
        return static::active()->ofType($type)->get();
    }

    public static function getGroupedContacts(): array
    {
        $contacts = static::active()->get();
        
        return [
            'addresses' => $contacts->where('type', self::TYPE_ADDRESS)->values()->map->toPublicArray(),
            'phones' => $contacts->where('type', self::TYPE_PHONE)->values()->map->toPublicArray(),
            'emails' => $contacts->where('type', self::TYPE_EMAIL)->values()->map->toPublicArray(),
            'socials' => $contacts->where('type', self::TYPE_SOCIAL)->values()->map->toPublicArray(),
        ];
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isAddress(): bool
    {
        return $this->type === self::TYPE_ADDRESS;
    }

    public function isPhone(): bool
    {
        return $this->type === self::TYPE_PHONE;
    }

    public function isEmail(): bool
    {
        return $this->type === self::TYPE_EMAIL;
    }

    public function isSocial(): bool
    {
        return $this->type === self::TYPE_SOCIAL;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $this->label,
            'value' => $this->value,
            'icon' => $this->icon,
            'link' => $this->link,
            'display_order' => $this->display_order,
        ];
    }
}
