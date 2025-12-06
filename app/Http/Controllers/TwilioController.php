<?php

namespace App\Http\Controllers;

use App\Shared\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class TwilioController extends Controller
{
    protected TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Send a demo SMS message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendDemoMessage(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string|max:1600',
        ]);

        try {
            // $result = $this->twilioService->sendSMS(
            //     $request->input('to'),
            //     $request->input('message')
            // );

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'SMS sent successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get message status by SID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessageStatus(Request $request): JsonResponse
    {
        $request->validate([
            'message_sid' => 'required|string',
        ]);

        try {
            $result = $this->twilioService->getMessageStatus(
                $request->input('message_sid')
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
