<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Payment\Services\StripeTransactionService;
use Illuminate\Http\Request;

class ClientTransactionController extends Controller
{
    public function __construct(protected StripeTransactionService $service) {}

    public function index()
    {
        return response()->json($this->service->listForClient());
    }

    public function show($id)
    {
        return response()->json($this->service->showForClient((int)$id));
    }

    public function refund($id)
    {
        return response()->json($this->service->refundForClient((int)$id));
    }
}
