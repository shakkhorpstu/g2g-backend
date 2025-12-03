<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Requests\CreateProfileRequest;
use Modules\Profile\Http\Requests\VerifyEmailChangeRequest;
use Modules\Profile\Http\Requests\SendTwoFactorRequest;
use Modules\Profile\Http\Requests\VerifyTwoFactorRequest;
use Modules\Profile\Services\UserProfileService;

class UserProfileController extends ApiController
{
    protected UserProfileService $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        parent::__construct();
        $this->userProfileService = $userProfileService;
    }

    public function index(): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->getProfile());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->updateProfile($request->getSanitizedData()));
    }

    public function verifyContactChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->verifyContactChange($request->getSanitizedData()));
    }

    public function destroy(): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->deleteProfile());
    }

    public function sendTwoFactor(SendTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->sendTwoFactor($request->getSanitizedData()), 'Two-factor OTP sent');
    }

    public function verifyTwoFactor(VerifyTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->verifyTwoFactor($request->getSanitizedData()), 'Two-factor enabled');
    }

    public function store(CreateProfileRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(fn() => $this->userProfileService->createInitialProfile(auth('api')->id(), $request->getSanitizedData()));
    }

    // Admin helpers kept minimal; reuse user service methods
    public function show(int $userId): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->getProfile());
    }

    public function updateById(UpdateProfileRequest $request, int $userId): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->updateProfile($request->getSanitizedData()));
    }

    public function destroyById(int $userId): JsonResponse
    {
        return $this->executeService(fn() => $this->userProfileService->deleteProfile());
    }
}
