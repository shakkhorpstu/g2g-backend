<?php

namespace App\Shared\Traits;

use Modules\Core\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAddresses
{
    /**
     * Get all addresses for the model
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the default address
     */
    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    /**
     * Get addresses by label
     */
    public function addressesByLabel(string $label)
    {
        return $this->addresses()->where('label', $label)->get();
    }

    /**
     * Set an address as default
     */
    public function setDefaultAddress(int $addressId): bool
    {
        $address = $this->addresses()->find($addressId);

        if (!$address) {
            return false;
        }

        // Unset other defaults
        $this->addresses()->where('id', '!=', $addressId)->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return true;
    }

    /**
     * Add a new address
     */
    public function addAddress(array $data): Address
    {
        return $this->addresses()->create($data);
    }

    /**
     * Check if model has any addresses
     */
    public function hasAddresses(): bool
    {
        return $this->addresses()->exists();
    }

    /**
     * Check if model has a default address
     */
    public function hasDefaultAddress(): bool
    {
        return $this->addresses()->where('is_default', true)->exists();
    }
}