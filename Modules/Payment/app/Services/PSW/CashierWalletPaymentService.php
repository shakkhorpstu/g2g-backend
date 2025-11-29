<?php

namespace Modules\Payment\Services\PSW;

use App\Shared\Services\BaseService;
use Modules\Payment\Models\StripeTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class CashierWalletPaymentService extends BaseService
{

    public function chargeViaWalletForPsw(array $data): array
    {
        $user = Auth::guard('psw-api')->user();
        if (!$user) { $this->fail('PSW not authenticated', 401); }

        // Explicitly drop unsupported/unsafe fields to avoid Stripe errors
        unset($data['type'], $data['payment_method_types']);

        $paymentMethodId = $data['payment_method_id'] ?? null;
        $amount = $data['amount'] ?? null;
        $currency = $data['currency'] ?? 'usd';
        $description = $data['description'] ?? null;

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

            $stripe = new StripeClient(config('cashier.secret'));
            $options = [
                'amount' => (int)round($amount * 100),
                'currency' => $currency,
                'customer' => $user->stripe_id,
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'payment_method_types' => ['card'],
                'return_url' => url('/payments/return'),
            ];
            if ($description) { $options['description'] = $description; }

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

            Log::debug('Wallet Charge Options (PSW)', $options);

            $payment = $stripe->paymentIntents->create($options);

            $transaction = StripeTransaction::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'stripe_payment_intent_id' => $payment->id,
                'stripe_charge_id' => $payment->charges->data[0]->id ?? null,
                'payment_method_id' => $paymentMethodId,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'description' => $description,
                'raw_payload' => ['payment' => $payment->toArray()],
            ]);

            if ($payment->status === 'requires_action' || $payment->status === 'requires_source_action') {
                return $this->success([
                    'transaction' => [
                        'id' => $transaction->id,
                        'payment_intent' => $transaction->stripe_payment_intent_id,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                    ],
                    'client_secret' => $payment->client_secret,
                    'next_action' => $payment->next_action,
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
            ], 'Wallet payment succeeded', 201);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $context = [
                'stripe_error' => [
                    'type' => method_exists($e, 'getError') ? ($e->getError()['type'] ?? null) : null,
                    'code' => method_exists($e, 'getError') ? ($e->getError()['code'] ?? null) : null,
                    'param' => method_exists($e, 'getError') ? ($e->getError()['param'] ?? null) : null,
                    'message' => $e->getMessage(),
                ],
                'options' => $options ?? null,
            ];
            $this->fail('Payment error: ' . $e->getMessage(), 502, $context);
        } catch (\Exception $e) {
            $this->fail('Payment error: ' . $e->getMessage(), 502);
        }
    }

    public function confirmPaymentForPsw(string $paymentIntentId): array
    {
        $user = Auth::guard('psw-api')->user();
        if (!$user) { $this->fail('PSW not authenticated', 401); }

        $transaction = StripeTransaction::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->first();

        if (!$transaction) { $this->fail('Transaction not found', 404); }

        try {
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
