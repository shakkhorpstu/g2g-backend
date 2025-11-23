<?php

namespace Modules\Payment\Services;

use App\Shared\Services\BaseService;
use Modules\Payment\Models\StripeTransaction;
use Stripe\Stripe;
use Stripe\Refund;
use Illuminate\Support\Facades\Auth;

class StripeTransactionService extends BaseService
{
    protected function initStripe(): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) { $this->fail('Stripe secret not configured', 500); }
        Stripe::setApiKey($secret);
    }

    public function listForClient(): array
    {
        $user = Auth::guard('api')->user();
        if (!$user) { $this->fail('User not authenticated', 401); }
        $transactions = StripeTransaction::query()
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn($t) => $this->format($t));
        return $this->success(['items' => $transactions], 'Transactions fetched');
    }

    public function listForPsw(): array
    {
        $user = Auth::guard('psw-api')->user();
        if (!$user) { $this->fail('PSW not authenticated', 401); }
        $transactions = StripeTransaction::query()
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn($t) => $this->format($t));
        return $this->success(['items' => $transactions], 'Transactions fetched');
    }

    public function showForClient(int $id): array
    {
        $user = Auth::guard('api')->user();
        if (!$user) { $this->fail('User not authenticated', 401); }
        $t = StripeTransaction::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->first();
        if (!$t) { $this->fail('Transaction not found', 404); }
        return $this->success($this->format($t), 'Transaction details');
    }

    public function showForPsw(int $id): array
    {
        $user = Auth::guard('psw-api')->user();
        if (!$user) { $this->fail('PSW not authenticated', 401); }
        $t = StripeTransaction::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->first();
        if (!$t) { $this->fail('Transaction not found', 404); }
        return $this->success($this->format($t), 'Transaction details');
    }

    public function refundForClient(int $id, ?int $amount = null): array
    {
        $user = Auth::guard('api')->user();
        if (!$user) { $this->fail('User not authenticated', 401); }
        return $this->refund($id, $user);
    }

    public function refundForPsw(int $id, ?int $amount = null): array
    {
        $user = Auth::guard('psw-api')->user();
        if (!$user) { $this->fail('PSW not authenticated', 401); }
        return $this->refund($id, $user);
    }

    protected function refund(int $id, $user): array
    {
        $t = StripeTransaction::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->first();
        if (!$t) { $this->fail('Transaction not found', 404); }
        if ($t->status === 'refunded') { $this->fail('Already refunded', 400); }
        if (!$t->stripe_charge_id) { $this->fail('Charge not found for refund', 400); }
        $this->initStripe();
        $refund = Refund::create(['charge' => $t->stripe_charge_id]);
        $t->status = 'refunded';
        $t->refunded_at = now();
        $raw = $t->raw_payload ?? [];
        $raw['refund'] = $refund->toArray();
        $t->raw_payload = $raw;
        $t->save();
        return $this->success($this->format($t), 'Refund processed');
    }

    protected function format(StripeTransaction $t): array
    {
        return [
            'id' => $t->id,
            'payment_intent' => $t->stripe_payment_intent_id,
            'charge_id' => $t->stripe_charge_id,
            'payment_method_id' => $t->payment_method_id,
            'amount' => $t->amount,
            'currency' => $t->currency,
            'status' => $t->status,
            'description' => $t->description,
            'refunded_at' => $t->refunded_at,
            'created_at' => $t->created_at,
        ];
    }
}
