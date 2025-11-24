<?php

namespace Modules\Payment\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payment\Services\Client\CardTransactionService;

class CardTransactionController extends Controller
{
    protected $service;

    public function __construct(CardTransactionService $service)
    {
        $this->service = $service;
    }

    public function index($payment_method_id)
    {
        return response()->json($this->service->list($payment_method_id));
    }

    public function show($payment_method_id, $transaction_id)
    {
        return response()->json($this->service->show($payment_method_id, $transaction_id));
    }

    public function store(Request $request, $payment_method_id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string',
            'description' => 'nullable|string',
            'name' => 'required|string',
            'address' => 'required|array',
            'address.line1' => 'required|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.postal_code' => 'required|string',
            'address.country' => 'required|string',
        ]);
        return response()->json($this->service->charge($payment_method_id, $validated));
    }
}
