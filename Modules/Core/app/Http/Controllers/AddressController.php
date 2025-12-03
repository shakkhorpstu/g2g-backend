<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\StoreAddressRequest;
use Modules\Core\Http\Requests\UpdateAddressRequest;
use Modules\Core\Http\Requests\UpdatePostalCodeRequest;
use Modules\Core\Services\AddressService;
use Illuminate\Http\JsonResponse;

/**
 * Address Controller
 * 
 * Handles address-related HTTP requests for authenticated users
 */
class AddressController extends ApiController
{
    /**
     * AddressService instance
     *
     * @var AddressService
     */
    protected AddressService $addressService;

    /**
     * AddressController constructor
     *
     * @param AddressService $addressService
     */
    public function __construct(AddressService $addressService)
    {
        parent::__construct();
        $this->addressService = $addressService;
    }

    /**
     * Get all addresses for authenticated user
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->getAllAddresses(),
            'Addresses retrieved successfully'
        );
    }

    /**
     * Store a new address
     *
     * @param StoreAddressRequest $request
     * @return JsonResponse
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->createAddress($request->validated()),
            'Address created successfully',
            201
        );
    }

    /**
     * Get a specific address
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->getAddress($id),
            'Address retrieved successfully'
        );
    }

    /**
     * Update an existing address
     *
     * @param UpdateAddressRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->updateAddress($id, $request->validated()),
            'Address updated successfully'
        );
    }

    /**
     * Delete an address
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->deleteAddress($id),
            'Address deleted successfully'
        );
    }

    /**
     * Set an address as default
     *
     * @param int $id
     * @return JsonResponse
     */
    public function setDefault(int $id): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->setAsDefault($id),
            'Default address updated successfully'
        );
    }

    /**
     * Get default address
     *
     * @return JsonResponse
     */
    public function getDefault(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->getDefaultAddress(),
            'Default address retrieved successfully'
        );
    }

    /**
     * Update or create postal code for default address
     *
     * @param UpdatePostalCodeRequest $request
     * @return JsonResponse
     */
    public function updatePostalCode(UpdatePostalCodeRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->addressService->updateOrCreatePostalCode($request->input('postal_code'))
        );
    }
}