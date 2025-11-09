<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Requests\RegisterRequest;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
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
}