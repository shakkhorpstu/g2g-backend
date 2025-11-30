<?php

namespace Modules\Core\Http\Controllers\PSW;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\RegistrationOtpService;

class PswRegistrationOtpController extends Controller
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
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        return response()->json($this->service->sendOtpForPsw($validated));
    }

    /**
     * Verify OTP code for PSW
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        return response()->json($this->service->verifyOtpForPsw($validated));
    }
}
