<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Requests\RegisterRequest;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
use Modules\Core\Http\Requests\VerifyAccountRequest;
use Modules\Core\Http\Requests\ForgotPasswordRequest;
use Modules\Core\Http\Requests\ResetPasswordRequest;
use App\Http\Controllers\ApiController;

/**
 * User Authentication Controller
 * 
 * Handles client/user authentication-related HTTP requests
 */
class UserAuthController extends ApiController
{
    /**
     * UserAuthService instance
     *
     * @var UserAuthService
     */
    protected UserAuthService $userAuthService;

    /**
     * UserAuthController constructor
     *
     * @param UserAuthService $userAuthService
     */
    public function __construct(UserAuthService $userAuthService)
    {
        parent::__construct();
        $this->userAuthService = $userAuthService;
    }

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->register($request->getSanitizedData()),
            'User registered successfully'
        );
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->login($request->getSanitizedData()),
            'Login successful'
        );
    }

    /**
     * Logout user
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->logout(),
            'Logout successful'
        );
    }

    /**
     * Refresh user token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->refresh(),
            'Token refreshed successfully'
        );
    }

    /**
     * Change user password
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->changePassword($request->getSanitizedData()),
            'Password changed successfully'
        );
    }

    /**
     * Verify account with OTP after registration
     *
     * @param VerifyAccountRequest $request
     * @return JsonResponse
     */
    public function verifyAccount(VerifyAccountRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->verifyAccount($request->getSanitizedData()),
            'Account verified successfully'
        );
    }

    /**
     * Request forgot password OTP
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->forgotPassword($request->getSanitizedData()),
            'Password reset OTP sent'
        );
    }

    /**
     * Reset password using OTP
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->resetPassword($request->getSanitizedData()),
            'Password reset successfully'
        );
    }
}