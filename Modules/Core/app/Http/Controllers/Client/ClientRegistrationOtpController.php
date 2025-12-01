<?php

namespace Modules\Core\Http\Controllers\Client;

use Illuminate\Http\JsonResponse;
use Modules\Core\App\Http\Requests\Client\SendOtpRequest;
use Modules\Core\App\Http\Requests\Client\VerifyOtpRequest;
use App\Http\Controllers\ApiController;
use Modules\Core\Services\RegistrationOtpService;

class ClientRegistrationOtpController extends ApiController
{
    public function __construct(protected RegistrationOtpService $service)
    {
    }

    /**
     * Send OTP to client's phone and/or email
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->service->sendOtpForClient($request->getSanitizedData()),
            'OTP sent to provided phone number' 
        );
    }

    /**
     * Verify OTP code for client
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->service->verifyOtpForClient($request->getSanitizedData()),
            'Your account has been verified successfully. Please log in'
        );
    }
}
