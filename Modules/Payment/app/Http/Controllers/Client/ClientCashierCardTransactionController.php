<?php

namespace Modules\Payment\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payment\Services\Client\CashierCardTransactionService;

class ClientCashierCardTransactionController extends Controller
{
    public function __construct(private CashierCardTransactionService $service)
    {
    }

    public function store(Request $request, string $payment_method_id)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'sometimes|string|size:3',
            'description' => 'sometimes|string|max:255',
        ]);

        return response()->json($this->service->makeTransaction($payment_method_id, $data));
    }
}
