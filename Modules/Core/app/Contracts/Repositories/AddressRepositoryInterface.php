<?php

namespace Modules\Core\Contracts\Repositories;

use Modules\Core\Models\Address;
use Illuminate\Database\Eloquent\Collection;

interface AddressRepositoryInterface
{
    /**
     * Get all addresses for an owner
     */
    public function getAllForOwner($owner);

    /**
     * Find address by ID
     */
    public function findById(int $id): ?Address;

    /**
     * Find address by ID for specific owner
     */
    public function findByIdForOwner(int $id, $owner): ?Address;

    /**
     * Get default address for owner
     */
    public function getDefaultForOwner($owner): ?Address;

    /**
     * Get addresses by label for owner
     */
    public function getByLabelForOwner(string $label, $owner): Collection;

    /**
     * Count addresses for owner
     */
    public function countForOwner($owner): int;

    /**
     * Create new address
     */
    public function create(array $data): Address;

    /**
     * Update address
     */
    public function update(Address $address, array $data): bool;

    /**
     * Delete address
     */
    public function delete(Address $address): bool;

    /**
     * Set address as default
     */
    public function setAsDefault(Address $address, $owner): bool;

    /**
     * Unset all defaults for owner
     */
    public function unsetDefaultsForOwner($owner): bool;

    /**
     * Set first address as default for owner
     */
    public function setFirstAsDefault($owner): void;
}