<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Http\Requests\PswRegisterRequest;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\VerifyAccountRequest;
use Modules\Core\Http\Requests\ForgotPasswordRequest;
use Modules\Core\Http\Requests\ResetPasswordRequest;
use Modules\Core\Services\PswAuthService;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PSW Authentication Controller
 * 
 * Handles Professional Service Worker (PSW) authentication endpoints
 */
class PswAuthController extends ApiController
{
    /**
     * PSW authentication service
     *
     * @var PswAuthService
     */
    protected PswAuthService $pswAuthService;

    /**
     * PswAuthController constructor
     *
     * @param PswAuthService $pswAuthService
     */
    public function __construct(PswAuthService $pswAuthService)
    {
        parent::__construct();
        $this->pswAuthService = $pswAuthService;
    }

    /**
     * Register a new PSW
     *
     * @param PswRegisterRequest $request
     * @return JsonResponse
     */
    public function register(PswRegisterRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->pswAuthService->register($request->validated());
        });
    }

    /**
     * Login PSW and return access token
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->pswAuthService->login($request->validated());
        });
    }

    /**
     * Logout PSW (revoke current token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->executeService(function () {
            return $this->pswAuthService->logout();
        });
    }

    /**
     * Refresh PSW token (revoke current and generate new)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        return $this->executeService(function () {
            return $this->pswAuthService->refresh();
        });
    }

    /**
     * Verify PSW account with OTP
     */
    public function verifyAccount(VerifyAccountRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->pswAuthService->verifyAccount($request->validated());
        });
    }

    /**
     * Request forgot password OTP for PSW
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->pswAuthService->forgotPassword($request->validated());
        });
    }

    /**
     * Reset PSW password using OTP
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->executeService(function () use ($request) {
            return $this->pswAuthService->resetPassword($request->validated());
        });
    }
}