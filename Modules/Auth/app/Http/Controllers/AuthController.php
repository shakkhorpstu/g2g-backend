<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Auth\Http\Requests\UserRegistrationRequest;
use Modules\Auth\Http\Requests\UserLoginRequest;
use Modules\Auth\Http\Requests\AdminLoginRequest;
use Modules\Auth\Http\Requests\UpdateProfileRequest;
use Modules\Auth\Services\AuthService;
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
     * Get User Profile API
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->executeService(
            fn() => $this->authService->getProfile($user)
        );
    }



    // =============== ADMIN AUTHENTICATION METHODS ===============

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
     * Get admin profile
     */
    public function adminProfile(Request $request): JsonResponse
    {
        $admin = $request->user();
        
        return $this->executeService(
            fn() => $this->authService->getProfile($admin)
        );
    }

    /**
     * Admin logout
     */
    public function adminLogout(Request $request): JsonResponse
    {
        $admin = $request->user();
        
        return $this->executeService(
            fn() => $this->authService->logout($admin)
        );
    }

    // =============== PROFILE MANAGEMENT METHODS ===============

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $sanitizedData = $request->getSanitizedData();

        return $this->executeServiceForUpdate(
            fn() => $this->authService->updateProfile($user, $sanitizedData)
        );
    }

    /**
     * Update admin profile
     */
    public function updateAdminProfile(UpdateProfileRequest $request): JsonResponse
    {
        $admin = $request->user();
        $sanitizedData = $request->getSanitizedData();

        return $this->executeServiceForUpdate(
            fn() => $this->authService->updateProfile($admin, $sanitizedData)
        );
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->executeService(
            fn() => $this->authService->logout($user)
        );
    }

    /**
     * Logout from all devices
     */
    public function logoutFromAllDevices(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->executeService(
            fn() => $this->authService->logoutAllDevices($user)
        );
    }
}
