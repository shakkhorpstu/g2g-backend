<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Payment\Services\StripeTransactionService;

class PswTransactionController extends Controller
{
    public function __construct(protected StripeTransactionService $service) {}

    public function index()
    {
        return response()->json($this->service->listForPsw());
    }

    public function show($id)
    {
        return response()->json($this->service->showForPsw((int)$id));
    }

    public function refund($id)
    {
        return response()->json($this->service->refundForPsw((int)$id));
    }
}
