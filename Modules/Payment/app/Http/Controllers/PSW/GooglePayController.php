<?php

namespace Modules\Payment\Http\Controllers\PSW;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payment\Services\GooglePayService;

class GooglePayController extends Controller
{
    public function __construct(protected GooglePayService $service) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
        ]);
        return response()->json($this->service->chargeViaGooglePay($validated, 'psw'), 201);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string'
        ]);
        return response()->json($this->service->finalizePaymentIntent($validated['payment_intent_id'], 'psw'));
    }
}
