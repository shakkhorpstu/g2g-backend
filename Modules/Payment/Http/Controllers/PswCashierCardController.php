<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payment\Services\CashierCardService;

class PswCashierCardController extends Controller
{
    public function __construct(private CashierCardService $service) {}

    public function index()
    {
        return response()->json($this->service->listForPsw());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_method_id' => 'required|string',
        ]);
        return response()->json($this->service->createForPsw($data));
    }

    public function show(string $payment_method_id)
    {
        return response()->json($this->service->showForPsw($payment_method_id));
    }

    public function update(Request $request, string $payment_method_id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:120',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:30',
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'sometimes|string|max:255',
            'address_city' => 'sometimes|string|max:80',
            'address_state' => 'sometimes|string|max:80',
            'address_postal_code' => 'sometimes|string|max:20',
            'address_country' => 'sometimes|string|size:2',
        ]);
        return response()->json($this->service->updateForPsw($payment_method_id, $data));
    }

    public function destroy(string $payment_method_id)
    {
        return response()->json($this->service->deleteForPsw($payment_method_id));
    }
}
