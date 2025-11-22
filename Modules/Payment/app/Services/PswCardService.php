<?php

namespace Modules\Payment\Services;

use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Exception\ApiErrorException;

class PswCardService extends BaseService
{
    protected function initStripe(): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) { $this->fail('Stripe secret not configured', 500); }
        Stripe::setApiKey($secret);
    }

    protected function getOrCreateCustomer($psw): string
    {
        $cacheKey = 'psw_stripe_customer_'.$psw->id;
        $existing = Cache::get($cacheKey);
        if ($existing) { return $existing; }
        $customer = Customer::create([
            'email' => $psw->email,
            'name' => trim($psw->first_name.' '.$psw->last_name),
        ]);
        Cache::put($cacheKey, $customer->id, now()->addDays(30));
        return $customer->id;
    }

    public function index(): array
    {
        $psw = Auth::guard('psw-api')->user();
        if (!$psw) { $this->fail('PSW not authenticated', 401); }
        $this->initStripe();
        try {
            $customerId = $this->getOrCreateCustomer($psw);
            $list = PaymentMethod::all(['customer' => $customerId, 'type' => 'card']);
            $defaultPmId = Customer::retrieve($customerId)->invoice_settings->default_payment_method;
            $cards = collect($list->data)->map(function ($pm) use ($defaultPmId) {
                return [
                    'id' => $pm->id,
                    'brand' => $pm->card->brand,
                    'last_four' => $pm->card->last4,
                    'exp_month' => $pm->card->exp_month,
                    'exp_year' => $pm->card->exp_year,
                    'is_default' => $defaultPmId === $pm->id,
                ];
            });
            return $this->success([
                'data' => $cards->toArray(),
                'pagination' => [
                    'current_page' => 1,
                    'total' => $cards->count(),
                    'per_page' => $cards->count(),
                ]
            ], 'Cards retrieved');
        } catch (ApiErrorException $e) { $this->fail('Stripe error: '.$e->getMessage(), 502); }
    }

    public function store(array $data): array
    {
        $psw = Auth::guard('psw-api')->user();
        if (!$psw) { $this->fail('PSW not authenticated', 401); }
        if (empty($data['payment_method_id'])) { $this->fail('payment_method_id is required', 422); }
        $this->initStripe();
        try {
            $customerId = $this->getOrCreateCustomer($psw);
            $pm = PaymentMethod::retrieve($data['payment_method_id']);
            $pm->attach(['customer' => $customerId]);
            if (!empty($data['is_default'])) {
                Customer::update($customerId, ['invoice_settings' => ['default_payment_method' => $pm->id]]);
            }
            return $this->success([
                'id' => $pm->id,
                'brand' => $pm->card->brand,
                'last_four' => $pm->card->last4,
                'exp_month' => $pm->card->exp_month,
                'exp_year' => $pm->card->exp_year,
                'is_default' => !empty($data['is_default']),
            ], 'Card created', 201);
        } catch (ApiErrorException $e) { $this->fail('Stripe error: '.$e->getMessage(), 502); }
    }

    public function show(string $id): array
    {
        $psw = Auth::guard('psw-api')->user();
        if (!$psw) { $this->fail('PSW not authenticated', 401); }
        $this->initStripe();
        try {
            $customerId = $this->getOrCreateCustomer($psw);
            $pm = PaymentMethod::retrieve($id);
            if ($pm->customer !== $customerId) { $this->fail('Card not found', 404); }
            $defaultPmId = Customer::retrieve($customerId)->invoice_settings->default_payment_method;
            return $this->success([
                'id' => $pm->id,
                'brand' => $pm->card->brand,
                'last_four' => $pm->card->last4,
                'exp_month' => $pm->card->exp_month,
                'exp_year' => $pm->card->exp_year,
                'is_default' => $defaultPmId === $pm->id,
            ], 'Card details');
        } catch (ApiErrorException $e) { $this->fail('Stripe error: '.$e->getMessage(), 502); }
    }

    public function update(string $id, array $data): array
    {
        $psw = Auth::guard('psw-api')->user();
        if (!$psw) { $this->fail('PSW not authenticated', 401); }
        $this->initStripe();
        try {
            $customerId = $this->getOrCreateCustomer($psw);
            $pm = PaymentMethod::retrieve($id);
            if ($pm->customer !== $customerId) { $this->fail('Card not found', 404); }
            if (!empty($data['is_default'])) {
                Customer::update($customerId, ['invoice_settings' => ['default_payment_method' => $pm->id]]);
            }
            $defaultPmId = Customer::retrieve($customerId)->invoice_settings->default_payment_method;
            return $this->success([
                'id' => $pm->id,
                'brand' => $pm->card->brand,
                'last_four' => $pm->card->last4,
                'exp_month' => $pm->card->exp_month,
                'exp_year' => $pm->card->exp_year,
                'is_default' => $defaultPmId === $pm->id,
            ], 'Card updated');
        } catch (ApiErrorException $e) { $this->fail('Stripe error: '.$e->getMessage(), 502); }
    }

    public function destroy(string $id): array
    {
        $psw = Auth::guard('psw-api')->user();
        if (!$psw) { $this->fail('PSW not authenticated', 401); }
        $this->initStripe();
        try {
            $customerId = $this->getOrCreateCustomer($psw);
            $pm = PaymentMethod::retrieve($id);
            if ($pm->customer !== $customerId) { $this->fail('Card not found', 404); }
            $defaultPmId = Customer::retrieve($customerId)->invoice_settings->default_payment_method;
            $pm->detach();
            if ($defaultPmId === $id) {
                $remaining = PaymentMethod::all(['customer' => $customerId, 'type' => 'card']);
                $newDefault = $remaining->data[0]->id ?? null;
                Customer::update($customerId, ['invoice_settings' => ['default_payment_method' => $newDefault]]);
            }
            return $this->success(null, 'Card deleted');
        } catch (ApiErrorException $e) { $this->fail('Stripe error: '.$e->getMessage(), 502); }
    }
}
