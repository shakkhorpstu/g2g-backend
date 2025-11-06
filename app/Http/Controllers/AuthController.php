<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends ApiController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register User API
     *
     * @param UserRegistrationRequest $request
     * @return JsonResponse
     */
    public function register(UserRegistrationRequest $request): JsonResponse
    {
        return $this->executeServiceForCreation(
            fn() => $this->authService->register($request->getSanitizedData())
        );
    }

    /**
     * Login User API
     *
     * @param UserLoginRequest $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->login($request->getSanitizedData())
        );
    }

    /**
     * Admin Login API
     *
     * @param AdminLoginRequest $request
     * @return JsonResponse
     */
    public function adminLogin(AdminLoginRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->adminLogin($request->getSanitizedData())
        );
    }

    /**
     * Get Authenticated User API
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->me()
        );
    }

    /**
     * Logout User API
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->logout()
        );
    }

    /**
     * Refresh Token API
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->refresh()
        );
    }

    /**
     * Change Password API
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->authService->changePassword($request->getSanitizedData())
        );
    }
}