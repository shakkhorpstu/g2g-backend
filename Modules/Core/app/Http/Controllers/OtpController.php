<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\ApiController;
use Modules\Core\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends ApiController
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        parent::__construct();
        $this->otpService = $otpService;
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'type' => 'required|string|in:account_verification,forgot_password,email_update,phone_update',
            'otpable_type' => 'required|string',
            'otpable_id' => 'required|integer'
        ]);

        return $this->executeService(
            fn() => $this->otpService->resendOtp(
                $request->identifier,
                $request->type,
                $request->otpable_type,
                $request->otpable_id
            )
        );
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp_code' => ['required','string','regex:/^\\d{4,6}$/'],
            'type' => 'required|string|in:account_verification,forgot_password,email_update,phone_update,2fa_login,2fa_enable'
        ]);

        return $this->executeService(
            fn() => $this->otpService->verifyOtp(
                $request->identifier,
                $request->otp_code,
                $request->type
            )
        );
    }
}