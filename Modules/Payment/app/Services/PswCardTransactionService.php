<?php

namespace Modules\Payment\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Charge;

class PswCardTransactionService
{
    public function list($payment_method_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = auth()->user();
        $customer = cache()->get('psw_stripe_customer_' . $user->id);
        if (!$customer) {
            abort(400, 'Stripe customer not found');
        }
        // List charges for the customer
        $charges = \Stripe\Charge::all([
            'customer' => $customer,
            'limit' => 100
        ]);
        // Filter by payment method
        $filtered = array_filter($charges->data, function($charge) use ($payment_method_id) {
            return $charge->payment_method === $payment_method_id;
        });
        return array_values($filtered);
    }

    public function show($payment_method_id, $transaction_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $charge = \Stripe\Charge::retrieve($transaction_id);
        if ($charge->payment_method !== $payment_method_id) {
            abort(404, 'Transaction not found for this card');
        }
        return $charge;
    }

    public function charge($payment_method_id, $data)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = auth()->user();
        $customer = cache()->get('psw_stripe_customer_' . $user->id);
        if (!$customer) {
            abort(400, 'Stripe customer not found');
        }
        $intent = PaymentIntent::create([
            'amount' => (int)($data['amount'] * 100),
            'currency' => $data['currency'],
            'customer' => $customer,
            'payment_method' => $payment_method_id,
            'off_session' => true,
            'confirm' => true,
            'description' => $data['description'] ?? null,
            'shipping' => [
                'name' => $data['name'],
                'address' => $data['address'],
            ],
        ]);
        return $intent;
    }
}
