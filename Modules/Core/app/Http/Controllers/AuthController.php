<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Requests\RegisterRequest;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\AdminLoginRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
use Modules\Core\Http\Requests\PswRegisterRequest;
use Modules\Core\Http\Requests\PswLoginRequest;
use Modules\Core\Http\Requests\VerifyTwoFactorRequest;
use App\Http\Controllers\ApiController;

/**
 * Authentication Controller
 * 
 * Handles authentication-related HTTP requests following the standardized API pattern
 */
class AuthController extends ApiController
{
    /**
     * AuthService instance
     *
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * AuthController constructor
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
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
            fn() => $this->authService->register($request->getSanitizedData()),
            'You have been registered successfully'
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
            fn() => $this->authService->login($request->getSanitizedData()),
            'Login successful'
        );
    }

    /**
     * Admin login
     *
     * @param AdminLoginRequest $request
     * @return JsonResponse
     */
    public function adminLogin(AdminLoginRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->adminLogin($request->getSanitizedData()),
            'Admin login successful'
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
            fn() => $this->authService->logout(),
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
            fn() => $this->authService->refresh(),
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
            fn() => $this->authService->changePassword($request->getSanitizedData()),
            'Password changed successfully'
        );
    }

    /**
     * Register a new PSW
     *
     * @param PswRegisterRequest $request
     * @return JsonResponse
     */
    public function pswRegister(PswRegisterRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->pswRegister($request->getSanitizedData()),
            'You have been registered successfully'
        );
    }

    /**
     * Login PSW
     *
     * @param PswLoginRequest $request
     * @return JsonResponse
     */
    public function pswLogin(PswLoginRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->pswLogin($request->getSanitizedData()),
            'PSW login successful'
        );
    }

    /**
     * Verify two-factor login OTP and create token
     */
    public function verifyTwoFactor(VerifyTwoFactorRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->verifyTwoFactor($request->getSanitizedData()),
            'Login successful'
        );
    }

    /**
     * Logout PSW
     *
     * @return JsonResponse
     */
    public function pswLogout(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->pswLogout(),
            'PSW logout successful'
        );
    }

    /**
     * Get PSW profile
     *
     * @return JsonResponse
     */
    public function getPswProfile(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->getPswProfile(),
            'PSW profile retrieved successfully'
        );
    }
}