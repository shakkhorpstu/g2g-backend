<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Profile\Http\Requests\ChangePasswordRequest;
use Modules\Core\Services\UserAuthService;
use Modules\Core\Services\PswAuthService;
use Modules\Core\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PasswordController extends ApiController
{
    protected UserAuthService $userAuthService;
    protected PswAuthService $pswAuthService;
    protected AdminAuthService $adminAuthService;

    public function __construct(
        UserAuthService $userAuthService,
        PswAuthService $pswAuthService,
        AdminAuthService $adminAuthService
    ) {
        parent::__construct();
        $this->userAuthService = $userAuthService;
        $this->pswAuthService = $pswAuthService;
        $this->adminAuthService = $adminAuthService;
    }

    /**
     * Change User Password API
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changeUserPassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->userAuthService->changePassword($request->getSanitizedData())
        );
    }

    /**
     * Change PSW Password API
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePswPassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->pswAuthService->changePassword($request->getSanitizedData())
        );
    }

    /**
     * Change Admin Password API
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changeAdminPassword(ChangePasswordRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->adminAuthService->changePassword($request->getSanitizedData())
        );
    }

    /**
     * Universal Change Password API (detects user type automatically)
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        // Check which guard has an authenticated user
        if (Auth::guard('api')->check()) {
            return $this->changeUserPassword($request);
        } elseif (Auth::guard('psw-api')->check()) {
            return $this->changePswPassword($request);
        } elseif (Auth::guard('admin-api')->check()) {
            return $this->changeAdminPassword($request);
        }

        return $this->errorResponse('User not authenticated', 401);
    }
}