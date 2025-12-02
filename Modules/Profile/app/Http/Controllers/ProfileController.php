<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Requests\CreateProfileRequest;
use Modules\Profile\Http\Requests\VerifyEmailChangeRequest; // handles both email & phone (can rename later)
use Modules\Profile\Http\Requests\SendTwoFactorRequest;
use Modules\Profile\Http\Requests\VerifyTwoFactorRequest;
use Modules\Profile\Http\Requests\SetLanguageRequest;
use Modules\Profile\Services\UserProfileService;
use Modules\Profile\Services\PswProfileService;
use Modules\Profile\Services\ProfileLanguageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends ApiController
{
    protected UserProfileService $userProfileService;
    protected PswProfileService $pswProfileService;
    protected ProfileLanguageService $languageService;

    public function __construct(
        UserProfileService $userProfileService,
        PswProfileService $pswProfileService,
        ProfileLanguageService $languageService
    ) {
        parent::__construct();
        $this->userProfileService = $userProfileService;
        $this->pswProfileService = $pswProfileService;
        $this->languageService = $languageService;
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
     * Verify contact (email or phone) change with OTP
     *
     * @param VerifyEmailChangeRequest $request
     * @return JsonResponse
     */
    public function verifyContactChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->verifyContactChange($request->getSanitizedData())
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

    /**
     * Verify PSW contact (email or phone) change with OTP
     *
     * @param VerifyEmailChangeRequest $request
     * @return JsonResponse
     */
    public function verifyPswContactChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->pswProfileService->verifyContactChange($request->getSanitizedData())
        );
    }

    /**
     * Send two-factor OTP for current authenticated user
     */
    public function sendTwoFactor(SendTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->sendTwoFactor($request->getSanitizedData()),
            'Two-factor OTP sent'
        );
    }

    /**
     * Verify two-factor OTP and enable 2FA for user
     */
    public function verifyTwoFactor(VerifyTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userProfileService->verifyTwoFactor($request->getSanitizedData()),
            'Two-factor enabled'
        );
    }

    /**
     * Send two-factor OTP for authenticated PSW
     */
    public function sendPswTwoFactor(SendTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->pswProfileService->sendTwoFactor($request->getSanitizedData()),
            'Two-factor OTP sent'
        );
    }

    /**
     * Verify two-factor OTP and enable 2FA for PSW
     */
    public function verifyPswTwoFactor(VerifyTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->pswProfileService->verifyTwoFactor($request->getSanitizedData()),
            'Two-factor enabled'
        );
    }

    /**
     * Get language preference for user
     *
     * @return JsonResponse
     */
    public function getLanguage(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->languageService->getLanguage(),
            'Language retrieved successfully'
        );
    }

    /**
     * Set language preference for user
     *
     * @param SetLanguageRequest $request
     * @return JsonResponse
     */
    public function setLanguage(SetLanguageRequest $request): JsonResponse
    {
        $languages = $request->input('languages', []);
        return $this->executeService(
            fn() => $this->languageService->setLanguage($languages),
            'Language updated successfully'
        );
    }
}