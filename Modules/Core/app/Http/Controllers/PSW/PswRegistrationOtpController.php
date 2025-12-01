<?php

namespace Modules\Core\Http\Controllers\PSW;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Requests\PSW\SendOtpRequest;
use Modules\Core\Http\Requests\PSW\VerifyOtpRequest;
use App\Http\Controllers\ApiController;
use Modules\Core\Services\RegistrationOtpService;

class PswRegistrationOtpController extends ApiController
{
    public function __construct(protected RegistrationOtpService $service)
    {
    }

    /**
     * Send OTP to PSW's phone and/or email
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->service->sendOtpForPsw($request->getSanitizedData()),
            'OTP sent to provided phone number'
        );
    }

    /**
     * Verify OTP code for PSW
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->service->verifyOtpForPsw($request->getSanitizedData()),
            'Your account has been verified successfully. Please log in'
        );
    }
}
