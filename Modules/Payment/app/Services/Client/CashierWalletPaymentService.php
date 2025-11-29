<?php

namespace Modules\Payment\Services\Client;

use App\Shared\Services\BaseService;
use Modules\Payment\Models\StripeTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class CashierWalletPaymentService extends BaseService
{
    public function chargeViaWalletForClient(array $data): array
{
    $user = Auth::guard('api')->user();
    if (!$user) { $this->fail('User not authenticated', 401); }
    
    // Drop unsafe fields
    unset($data['type'], $data['payment_method_types']);

    $paymentMethodId = $data['payment_method_id'] ?? null;
    $amount = $data['amount'] ?? null;
    $currency = $data['currency'] ?? 'usd';
    $description = $data['description'] ?? null;

    // Validation (already done in controller, but keep for safety)
    if (!$paymentMethodId) { $this->fail('payment_method_id is required', 422); }
    if (!$amount || !is_numeric($amount) || $amount <= 0) {
        $this->fail('amount must be positive numeric', 422);
    }

    try {
        if (!$user->hasPaymentMethod($paymentMethodId)) {
            $user->addPaymentMethod($paymentMethodId);
        }
        if (!isset($user->stripe_id) || !$user->stripe_id) {
            $user->createOrGetStripeCustomer();
        }

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        $options = [
            'amount' => (int)round($amount * 100),
            'currency' => $currency,
            'customer' => $user->stripe_id,
            'payment_method' => $paymentMethodId,
            'confirm' => true,
            'return_url' => url('/payments/return'),
        ];
        if ($description) { $options['description'] = $description; }
        // Optionally include shipping info
        if (!empty($data['name']) || !empty($data['address'])) {
            $options['shipping'] = [
                'name' => $data['name'] ?? null,
                'address' => [
                    'line1' => $data['address']['line1'] ?? null,
                    'city' => $data['address']['city'] ?? null,
                    'state' => $data['address']['state'] ?? null,
                    'postal_code' => $data['address']['postal_code'] ?? null,
                    'country' => $data['address']['country'] ?? null,
                ],
            ];
        }

        // --- DO NOT INCLUDE 'payment_method_types' or 'type' here! ---

        \Log::debug('Wallet Charge Options', $options);

        $payment = $stripe->paymentIntents->create($options);

        // ... Save and respond as you do in your code ...
        return $this->success(['stripe_payment_intent_id' => $payment->id], 'Wallet payment succeeded', 201);

    } catch (\Stripe\Exception\ApiErrorException $e) {
        $this->fail('Payment error: ' . $e->getMessage(), 502, [
            'stripe_error' => [
                'type' => method_exists($e, 'getError') ? ($e->getError()['type'] ?? null) : null,
                'code' => method_exists($e, 'getError') ? ($e->getError()['code'] ?? null) : null,
                'param' => method_exists($e, 'getError') ? ($e->getError()['param'] ?? null) : null,
                'message' => $e->getMessage(),
            ],
        ]);
    } catch (\Exception $e) {
        $this->fail('Payment error: ' . $e->getMessage(), 502);
    }
}

    public function confirmPaymentForClient(string $paymentIntentId): array
    {
        $user = Auth::guard('api')->user();
        if (!$user) { $this->fail('User not authenticated', 401); }

        $transaction = StripeTransaction::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->first();

        if (!$transaction) { $this->fail('Transaction not found', 404); }

        try {
            // Retrieve latest payment intent status from Stripe
            $paymentIntent = $user->findPaymentIntent($paymentIntentId);
            $charge = $paymentIntent->charges->data[0] ?? null;

            $transaction->status = $paymentIntent->status;
            if ($charge && !$transaction->stripe_charge_id) {
                $transaction->stripe_charge_id = $charge->id;
            }
            $raw = $transaction->raw_payload ?? [];
            $raw['latest_payment_intent'] = $paymentIntent->toArray();
            $transaction->raw_payload = $raw;
            $transaction->save();

            return $this->success([
                'transaction' => [
                    'id' => $transaction->id,
                    'stripe_payment_intent_id' => $transaction->stripe_payment_intent_id,
                    'stripe_charge_id' => $transaction->stripe_charge_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                ],
                'requires_action' => in_array($transaction->status, ['requires_action', 'requires_source_action']),
            ], $transaction->status === 'succeeded' ? 'Payment succeeded' : 'Payment status updated');
        } catch (\Exception $e) {
            $this->fail('Confirmation error: ' . $e->getMessage(), 502);
        }
    }
}
