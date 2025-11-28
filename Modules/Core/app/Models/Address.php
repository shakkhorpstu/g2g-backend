<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'label',
        'address_line',
        'city',
        'province',
        'postal_code',
        'country_id',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'country_id' => 'integer',
    ];

    /**
     * Get the parent addressable model (User, PSW, Admin, etc.)
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the country for this address
     */
    public function country()
    {
        return $this->belongsTo(\Modules\Core\Models\Country::class, 'country_id');
    }

    /**
     * Get full formatted address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if address has coordinates
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinatesAttribute(): ?array
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    /**
     * Scope for default address
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for addresses by label
     */
    public function scopeByLabel($query, string $label)
    {
        return $query->where('label', $label);
    }

    /**
     * Scope for addresses by owner
     */
    public function scopeForOwner($query, $owner)
    {
        return $query->where('addressable_type', get_class($owner))
                     ->where('addressable_id', $owner->id);
    }

    /**
     * Boot method to handle default address logic
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new default address, unset other defaults
        static::creating(function ($address) {
            if ($address->is_default) {
                static::where('addressable_type', $address->addressable_type)
                      ->where('addressable_id', $address->addressable_id)
                      ->where('is_default', true)
                      ->update(['is_default' => false]);
            }
        });

        // When updating to default, unset other defaults
        static::updating(function ($address) {
            if ($address->is_default && $address->isDirty('is_default')) {
                static::where('addressable_type', $address->addressable_type)
                      ->where('addressable_id', $address->addressable_id)
                      ->where('id', '!=', $address->id)
                      ->where('is_default', true)
                      ->update(['is_default' => false]);
            }
        });
    }
}