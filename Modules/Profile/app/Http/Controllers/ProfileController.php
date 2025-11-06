<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Requests\CreateProfileRequest;
use Modules\Profile\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends ApiController
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get User Profile API
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profileService->getProfile()
        );
    }

    /**
     * Update User Profile API
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profileService->updateProfile($request->getSanitizedData())
        );
    }

    /**
     * Delete User Profile API
     *
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profileService->deleteProfile()
        );
    }

    /**
     * Create User Profile API (Admin only)
     *
     * @param CreateProfileRequest $request
     * @return JsonResponse
     */
    public function store(CreateProfileRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(
            fn() => $this->profileService->createProfile($request->getSanitizedData())
        );
    }

    /**
     * Get Profile by ID API (Admin only)
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function show(int $userId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profileService->getProfile($userId)
        );
    }

    /**
     * Update Profile by ID API (Admin only)
     *
     * @param UpdateProfileRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function updateById(UpdateProfileRequest $request, int $userId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profileService->updateProfile($request->getSanitizedData(), $userId)
        );
    }

    /**
     * Delete Profile by ID API (Admin only)
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function destroyById(int $userId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->profileService->deleteProfile($userId)
        );
    }
}