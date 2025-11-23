<?php

namespace Modules\Payment\Services;

use App\Shared\Services\BaseService;
use Modules\Payment\Models\StripeTransaction;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Auth;

class GooglePayService extends BaseService
{
    protected function initStripe(): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) { $this->fail('Stripe secret not configured', 500); }
        Stripe::setApiKey($secret);
    }

    protected function getOrCreateClientCustomer($user): string
    {
        $meta = $user->meta ?? [];
        if (!empty($meta['stripe_customer_id'])) {
            return $meta['stripe_customer_id'];
        }
        $customer = Customer::create([
            'email' => $user->email,
            'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
        ]);
        $meta['stripe_customer_id'] = $customer->id;
        $user->meta = $meta;
        $user->save();
        return $customer->id;
    }

    protected function getOrCreatePswCustomer($psw): string
    {
        $cacheKey = 'psw_stripe_customer_'.$psw->id;
        $existing = cache()->get($cacheKey);
        if ($existing) { return $existing; }
        $customer = Customer::create([
            'email' => $psw->email,
            'name' => trim(($psw->first_name ?? '').' '.($psw->last_name ?? '')),
        ]);
        cache()->put($cacheKey, $customer->id, now()->addDays(30));
        return $customer->id;
    }

    public function chargeViaGooglePay(array $data, string $context = 'client'): array
    {
        $token = $data['token'] ?? null;
        $amount = $data['amount'] ?? null;
        $currency = $data['currency'] ?? null;
        $description = $data['description'] ?? null;
        if (!$token) { $this->fail('token is required', 422); }
        if (!$amount || !is_numeric($amount) || $amount <= 0) { $this->fail('amount must be positive numeric', 422); }
        if (!$currency) { $this->fail('currency is required', 422); }

        $this->initStripe();

        // Determine user based on context
        $user = $context === 'psw'
            ? Auth::guard('psw-api')->user()
            : Auth::guard('api')->user();
        if (!$user) { $this->fail('User not authenticated', 401); }

        try {
            $customerId = $context === 'psw'
                ? $this->getOrCreatePswCustomer($user)
                : $this->getOrCreateClientCustomer($user);

            // Create and confirm PaymentIntent using Google Pay token
            $intent = PaymentIntent::create([
                'amount' => (int)($amount * 100), // smallest currency unit
                'currency' => $currency,
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => [ 'token' => $token ],
                ],
                'confirmation_method' => 'automatic',
                'confirm' => true,
                'description' => $description,
            ]);
            $charge = $intent->charges->data[0] ?? null;
            $status = $intent->status;

            // Persist initial transaction record
            $transaction = StripeTransaction::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'stripe_payment_intent_id' => $intent->id,
                'stripe_charge_id' => $charge?->id,
                'payment_method_id' => $intent->payment_method,
                'amount' => $intent->amount,
                'currency' => $intent->currency,
                'status' => $status,
                'description' => $description,
                'raw_payload' => [
                    'payment_intent' => $intent->toArray(),
                    'charge' => $charge?->toArray(),
                ],
            ]);

            // If additional customer action required (3DS/SCA)
            if ($status === 'requires_action' || $status === 'requires_source_action') {
                return $this->success([
                    'transaction' => [
                        'id' => $transaction->id,
                        'payment_intent' => $transaction->stripe_payment_intent_id,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                    ],
                    'client_secret' => $intent->client_secret,
                    'next_action' => $intent->next_action,
                ], 'Additional authentication required', 202);
            }

            return $this->success([
                'transaction' => [
                    'id' => $transaction->id,
                    'stripe_payment_intent_id' => $transaction->stripe_payment_intent_id,
                    'stripe_charge_id' => $transaction->stripe_charge_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                ]
            ], 'Google Pay charge succeeded', 201);
        } catch (ApiErrorException $e) {
            $this->fail('Stripe error: '.$e->getMessage(), 502);
        }
    }

    public function finalizePaymentIntent(string $paymentIntentId, string $context = 'client'): array
    {
        if (empty($paymentIntentId)) { $this->fail('payment_intent_id is required', 422); }
        $this->initStripe();
        $user = $context === 'psw' ? Auth::guard('psw-api')->user() : Auth::guard('api')->user();
        if (!$user) { $this->fail('User not authenticated', 401); }

        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
            $charge = $intent->charges->data[0] ?? null;
            $transaction = StripeTransaction::query()
                ->where('stripe_payment_intent_id', $paymentIntentId)
                ->where('user_id', $user->id)
                ->where('user_type', get_class($user))
                ->first();
            if (!$transaction) { $this->fail('Transaction record not found', 404); }

            // Update transaction if succeeded
            $transaction->status = $intent->status;
            if ($charge && !$transaction->stripe_charge_id) {
                $transaction->stripe_charge_id = $charge->id;
            }
            $raw = $transaction->raw_payload ?? [];
            $raw['latest_payment_intent'] = $intent->toArray();
            if ($charge) { $raw['latest_charge'] = $charge->toArray(); }
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
                'requires_action' => in_array($transaction->status, ['requires_action','requires_source_action']),
            ], $transaction->status === 'succeeded' ? 'Payment succeeded' : 'Payment status updated');
        } catch (ApiErrorException $e) {
            $this->fail('Stripe error: '.$e->getMessage(), 502);
        }
    }
}
