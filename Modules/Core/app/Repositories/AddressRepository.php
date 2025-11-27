<?php

namespace Modules\Core\Repositories;

use Modules\Core\Models\Address;
use Modules\Core\Contracts\Repositories\AddressRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Address Repository
 * 
 * Implementation of address-specific database operations
 */
class AddressRepository implements AddressRepositoryInterface
{
    /**
     * Get all addresses for an owner
     *
     * @param mixed $owner
     */
    public function getAllForOwner($owner)
    {
        return DB::table('addresses')
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($address) => Address::find($address->id));
    }

    /**
     * Find address by ID
     *
     * @param int $id
     * @return Address|null
     */
    public function findById(int $id): ?Address
    {
        return Address::find($id);
    }

    /**
     * Find address by ID for specific owner
     *
     * @param int $id
     * @param mixed $owner
     * @return Address|null
     */
    public function findByIdForOwner(int $id, $owner): ?Address
    {
        $result = DB::table('addresses')
            ->where('id', $id)
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->first();

        return $result ? Address::find($result->id) : null;
    }

    /**
     * Get default address for owner
     *
     * @param mixed $owner
     * @return Address|null
     */
    public function getDefaultForOwner($owner): ?Address
    {
        $result = DB::table('addresses')
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->where('is_default', true)
            ->first();

        return $result ? Address::find($result->id) : null;
    }

    /**
     * Get addresses by label for owner
     *
     * @param string $label
     * @param mixed $owner
     * @return Collection
     */
    public function getByLabelForOwner(string $label, $owner): Collection
    {
        return DB::table('addresses')
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->where('label', $label)
            ->get()
            ->map(fn($address) => Address::find($address->id));
    }

    /**
     * Count addresses for owner
     *
     * @param mixed $owner
     * @return int
     */
    public function countForOwner($owner): int
    {
        return DB::table('addresses')
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->count();
    }

    /**
     * Create new address
     *
     * @param array $data
     * @return Address
     */
    public function create(array $data): Address
    {
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        $id = DB::table('addresses')->insertGetId($data);
        return Address::find($id);
    }

    /**
     * Update address
     *
     * @param Address $address
     * @param array $data
     * @return bool
     */
    public function update(Address $address, array $data): bool
    {
        $data['updated_at'] = now();
        
        return DB::table('addresses')
            ->where('id', $address->id)
            ->update($data) > 0;
    }

    /**
     * Delete address
     *
     * @param Address $address
     * @return bool
     */
    public function delete(Address $address): bool
    {
        return DB::table('addresses')
            ->where('id', $address->id)
            ->delete() > 0;
    }

    /**
     * Set address as default
     *
     * @param Address $address
     * @param mixed $owner
     * @return bool
     */
    public function setAsDefault(Address $address, $owner): bool
    {
        // Unset other defaults
        $this->unsetDefaultsForOwner($owner);

        // Set this as default
        return DB::table('addresses')
            ->where('id', $address->id)
            ->update([
                'is_default' => true,
                'updated_at' => now()
            ]) > 0;
    }

    /**
     * Unset all defaults for owner
     *
     * @param mixed $owner
     * @return bool
     */
    public function unsetDefaultsForOwner($owner): bool
    {
        return DB::table('addresses')
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->where('is_default', true)
            ->update([
                'is_default' => false,
                'updated_at' => now()
            ]) !== false;
    }

    /**
     * Set first address as default for owner
     *
     * @param mixed $owner
     * @return void
     */
    public function setFirstAsDefault($owner): void
    {
        $firstAddress = DB::table('addresses')
            ->where('addressable_type', get_class($owner))
            ->where('addressable_id', $owner->id)
            ->orderBy('created_at', 'asc')
            ->first();

        if ($firstAddress) {
            DB::table('addresses')
                ->where('id', $firstAddress->id)
                ->update([
                    'is_default' => true,
                    'updated_at' => now()
                ]);
        }
    }
}