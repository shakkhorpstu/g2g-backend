<?php

namespace Modules\Payment\Services\PSW;

use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class CashierCardTransactionService extends BaseService
{
    protected function ensurePaymentMethodOwned($user, string $paymentMethodId): void
    {
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) {
            $this->fail('Payment method not found for this user', 404);
        }
    }

    public function makeTransaction(string $paymentMethodId, array $data): array
    {
        $user = Auth::guard('psw-api')->user();
        if (!$user) { $this->fail('PSW not authenticated', 401); }
        $this->ensurePaymentMethodOwned($user, $paymentMethodId);

        $amount = (int) round(($data['amount'] ?? 0) * 100);
        if ($amount <= 0) { $this->fail('amount must be positive', 422); }
        $currency = $data['currency'] ?? 'usd';
        $description = $data['description'] ?? null;

        $user->createOrGetStripeCustomer();
        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        // Build payment intent options
        $options = [
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $user->stripe_id,
            'payment_method' => $paymentMethodId,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ];

        if ($description) {
            $options['description'] = $description;
        }

        // Add shipping details if provided (for Indian regulations)
        if (isset($data['name']) || isset($data['address'])) {
            $shipping = [];
            if (isset($data['name'])) {
                $shipping['name'] = $data['name'];
            }
            if (isset($data['address'])) {
                $shipping['address'] = $data['address'];
            }
            if (!empty($shipping)) {
                $options['shipping'] = $shipping;
            }
        }

        $pi = $stripe->paymentIntents->create($options);

        return $this->success([
            'payment_intent_id' => $pi->id,
            'status' => $pi->status,
            'amount' => $pi->amount,
            'currency' => $pi->currency,
            'client_secret' => $pi->client_secret ?? null,
        ], $pi->status === 'succeeded' ? 'Charge succeeded' : 'Charge created');
    }
}
