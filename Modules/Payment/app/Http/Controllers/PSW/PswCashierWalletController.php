<?php

namespace Modules\Payment\Http\Controllers\PSW;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payment\Services\CashierWalletPaymentService;

class PswCashierWalletController extends Controller
{
    public function __construct(protected CashierWalletPaymentService $service) {}

    public function charge(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string',
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
        ]);
        return response()->json($this->service->chargeViaWalletForPsw($validated));
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string'
        ]);
        return response()->json($this->service->confirmPaymentForPsw($validated['payment_intent_id']));
    }
}
