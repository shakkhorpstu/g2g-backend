<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Address;
use App\Shared\Services\BaseService;
use Modules\Core\Contracts\Repositories\AddressRepositoryInterface;

/**
 * Address Service
 * 
 * Handles address management business logic for User and PSW models
 */
class AddressService extends BaseService
{
    /**
     * Address repository instance
     *
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * Allowed guards for address management
     *
     * @var array
     */
    protected array $allowedGuards = ['api', 'psw-api'];

    /**
     * AddressService constructor
     *
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(AddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Get all addresses for authenticated user
     *
     * @return array
     */
    public function getAllAddresses(): array
    {
        return $this->execute(function () {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $addresses = $this->addressRepository->getAllForOwner($user);

            return $this->success($addresses, 'Addresses retrieved successfully');
        });
    }

    /**
     * Get address by ID
     *
     * @param int $id
     * @return array
     */
    public function getAddress(int $id): array
    {
        return $this->execute(function () use ($id) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $address = $this->addressRepository->findByIdForOwner($id, $user);

            if (!$address) {
                $this->fail('Address not found', 404);
            }

            return $this->success($address, 'Address retrieved successfully');
        });
    }

    /**
     * Get default address
     *
     * @return array
     */
    public function getDefaultAddress(): array
    {
        return $this->execute(function () {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $address = $this->addressRepository->getDefaultForOwner($user);

            if (!$address) {
                $this->fail('No default address found', 404);
            }

            return $this->success($address, 'Default address retrieved successfully');
        });
    }

    /**
     * Create a new address
     *
     * @param array $data
     * @return array
     */
    public function createAddress(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);

            // Add owner information
            $data['addressable_type'] = get_class($user);
            $data['addressable_id'] = $user->id;

            // If this is marked as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                $this->addressRepository->unsetDefaultsForOwner($user);
            }

            // If this is the first address, make it default
            if (!isset($data['is_default'])) {
                $addressCount = $this->addressRepository->countForOwner($user);
                if ($addressCount === 0) {
                    $data['is_default'] = true;
                }
            }

            $address = $this->addressRepository->create($data);

            return $this->success($address, 'Address created successfully', 201);
        });
    }

    /**
     * Update an existing address
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateAddress(int $id, array $data): array
    {
        return $this->executeWithTransaction(function () use ($id, $data) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $address = $this->addressRepository->findByIdForOwner($id, $user);

            if (!$address) {
                $this->fail('Address not found', 404);
            }

            // If setting as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                $this->addressRepository->unsetDefaultsForOwner($user);
            }

            // Don't allow unsetting default if it's the only address
            if (isset($data['is_default']) && !$data['is_default'] && $address->is_default) {
                $addressCount = $this->addressRepository->countForOwner($user);
                if ($addressCount === 1) {
                    $data['is_default'] = true;
                }
            }

            $this->addressRepository->update($address, $data);
            $address->refresh();

            return $this->success($address, 'Address updated successfully');
        });
    }

    /**
     * Delete an address
     *
     * @param int $id
     * @return array
     */
    public function deleteAddress(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $address = $this->addressRepository->findByIdForOwner($id, $user);

            if (!$address) {
                $this->fail('Address not found', 404);
            }

            $wasDefault = $address->is_default;
            $this->addressRepository->delete($address);

            // If deleted address was default, set another as default
            if ($wasDefault) {
                $this->addressRepository->setFirstAsDefault($user);
            }

            return $this->success(null, 'Address deleted successfully');
        });
    }

    /**
     * Set an address as default
     *
     * @param int $id
     * @return array
     */
    public function setAsDefault(int $id): array
    {
        return $this->executeWithTransaction(function () use ($id) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);
            $address = $this->addressRepository->findByIdForOwner($id, $user);

            if (!$address) {
                $this->fail('Address not found', 404);
            }

            $this->addressRepository->setAsDefault($address, $user);

            return $this->success(null, 'Default address updated successfully');
        });
    }

    /**
     * Update or create postal code for default address
     *
     * @param string $postalCode
     * @return array
     */
    public function updateOrCreatePostalCode(string $postalCode): array
    {
        return $this->executeWithTransaction(function () use ($postalCode) {
            $user = $this->getAuthenticatedUserOrFail($this->allowedGuards);

            // Check if default address exists
            $defaultAddress = $this->addressRepository->getDefaultForOwner($user);

            if ($defaultAddress) {
                // Update existing default address
                $this->addressRepository->update($defaultAddress, [
                    'postal_code' => $postalCode,
                ]);
                $defaultAddress->refresh();

                return $this->success($defaultAddress, 'Postal code updated successfully');
            } else {
                // Create new address with postal code as default
                $data = [
                    'addressable_type' => get_class($user),
                    'addressable_id' => $user->id,
                    'postal_code' => $postalCode,
                    'is_default' => true,
                    'address_line' => 'N/A',
                    'city' => 'N/A',
                    'province' => 'N/A',
                    'country_id' => 1
                ];

                $address = $this->addressRepository->create($data);

                return $this->success($address, 'Default address created with postal code', 201);
            }
        });
    }
}