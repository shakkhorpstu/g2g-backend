<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Http\Requests\PswRegisterRequest;
use Modules\Core\Http\Requests\LoginRequest;
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
}