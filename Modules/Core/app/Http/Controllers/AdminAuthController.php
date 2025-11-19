<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Http\Requests\AdminLoginRequest;
use Modules\Core\Http\Requests\ForgotPasswordRequest;
use Modules\Core\Http\Requests\ResetPasswordRequest;
use Modules\Core\Services\AdminAuthService;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin Authentication Controller
 * 
 * Handles administrator authentication endpoints
 */
class AdminAuthController extends ApiController
{
    /**
     * Admin authentication service
     *
     * @var AdminAuthService
     */
    protected AdminAuthService $adminAuthService;

    /**
     * AdminAuthController constructor
     *
     * @param AdminAuthService $adminAuthService
     */
    public function __construct(AdminAuthService $adminAuthService)
    {
        parent::__construct();
        $this->adminAuthService = $adminAuthService;
    }

    /**
     * Login admin and return access token
     *
     * @param AdminLoginRequest $request
     * @return JsonResponse
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->adminAuthService->login($request->validated());
        });
    }

    /**
     * Logout admin (revoke current token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->executeService(function () {
            return $this->adminAuthService->logout();
        });
    }

    /**
     * Refresh admin token (revoke current and generate new)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        return $this->executeService(function () {
            return $this->adminAuthService->refresh();
        });
    }

    /**
     * Get current admin profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        return $this->executeService(function () {
            return $this->adminAuthService->getProfile();
        });
    }

    /**
     * Request forgot password OTP for admin
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->adminAuthService->forgotPassword($request->validated());
        });
    }

    /**
     * Reset admin password using OTP
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->adminAuthService->resetPassword($request->validated());
        });
    }
}