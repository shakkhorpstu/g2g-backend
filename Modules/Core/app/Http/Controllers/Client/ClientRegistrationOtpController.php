<?php

namespace Modules\Core\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\RegistrationOtpService;

class ClientRegistrationOtpController extends Controller
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
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'phone' => 'required|string|min:6|max:20',
        ]);

        return response()->json($this->service->sendOtpForClient($validated));
    }

    /**
     * Verify OTP code for client
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'otp_code' => 'required|string|size:6',
        ]);

        return response()->json($this->service->verifyOtpForClient($validated));
    }
}
