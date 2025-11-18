<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Requests\CreateProfileRequest;
use Modules\Profile\Http\Requests\VerifyEmailChangeRequest;
use Modules\Profile\Services\UserProfileService;
use Modules\Profile\Services\PswProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends ApiController
{
    protected UserProfileService $userProfileService;
    protected PswProfileService $pswProfileService;

    public function __construct(UserProfileService $userProfileService, PswProfileService $pswProfileService)
    {
        parent::__construct();
        $this->userProfileService = $userProfileService;
        $this->pswProfileService = $pswProfileService;
    }

    /**
     * Get User Profile API
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->getProfile()
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
            fn() => $this->userProfileService->updateProfile($request->getSanitizedData())
        );
    }

    /**
     * Verify email change with OTP
     *
     * @param VerifyEmailChangeRequest $request
     * @return JsonResponse
     */
    public function verifyEmailChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->verifyEmailChange($request->getSanitizedData())
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
            fn() => $this->userProfileService->deleteProfile()
        );
    }

    /**
     * Create User Profile API (Admin only)
     * TODO: Implement proper admin functionality
     *
     * @param CreateProfileRequest $request
     * @return JsonResponse
     */
    public function store(CreateProfileRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(
            fn() => $this->userProfileService->createInitialProfile(auth('api')->id(), $request->getSanitizedData())
        );
    }

    /**
     * Get Profile by ID API (Admin only)
     * TODO: Implement proper admin functionality
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function show(int $userId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->getProfile()
        );
    }

    /**
     * Update Profile by ID API (Admin only)
     * TODO: Implement proper admin functionality
     *
     * @param UpdateProfileRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function updateById(UpdateProfileRequest $request, int $userId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->updateProfile($request->getSanitizedData())
        );
    }

    /**
     * Delete Profile by ID API (Admin only)
     * TODO: Implement proper admin functionality
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function destroyById(int $userId): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->deleteProfile()
        );
    }

    /**
     * Get PSW Profile API
     *
     * @return JsonResponse
     */
    public function pswProfile(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->pswProfileService->getProfile()
        );
    }

    /**
     * Update PSW Profile API
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updatePswProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->pswProfileService->updateProfile($request->getSanitizedData())
        );
    }
}