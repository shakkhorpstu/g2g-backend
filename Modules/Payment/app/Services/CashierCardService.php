<?php

namespace Modules\Payment\Services;

use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

/**
 * Cashier-based card (payment method) CRUD operations without touching existing Stripe direct controllers.
 * @method \Laravel\Cashier\Cashier createOrGetStripeCustomer()
 * @method \Stripe\Service\CustomerService stripe()
 * @method mixed findPaymentMethod(string $paymentMethodId)
 */
class CashierCardService extends BaseService
{
    protected function user(string $guard)
    {
        $user = Auth::guard($guard)->user();
        if (!$user) {
            $this->fail('Not authenticated', 401);
        }
        if (!method_exists($user, 'createOrGetStripeCustomer')) {
            $this->fail('Billable trait not present on user model', 500);
        }
        $user->createOrGetStripeCustomer();
        return $user;
    }

    public function listForClient(): array
    {
        $user = $this->user('api');
        $methods = $user->paymentMethods();
        $items = collect($methods)->map(fn($pm) => $this->mapPaymentMethod($pm))->values();
        return $this->success(['items' => $items], 'Cashier cards retrieved');
    }

    public function listForPsw(): array
    {
        $user = $this->user('psw-api');
        $methods = $user->paymentMethods();
        $items = collect($methods)->map(fn($pm) => $this->mapPaymentMethod($pm))->values();
        return $this->success(['items' => $items], 'Cashier cards retrieved');
    }

    public function showForClient(string $paymentMethodId): array
    {
        $user = $this->user('api');
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) { $this->fail('Payment method not found', 404); }
        return $this->success($this->mapPaymentMethod($pm), 'Cashier card details');
    }

    public function showForPsw(string $paymentMethodId): array
    {
        $user = $this->user('psw-api');
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) { $this->fail('Payment method not found', 404); }
        return $this->success($this->mapPaymentMethod($pm), 'Cashier card details');
    }

    public function createForClient(array $data): array
    {
        $user = $this->user('api');
        $paymentMethodId = $data['payment_method_id'] ?? null;
        if (!$paymentMethodId) { $this->fail('payment_method_id required', 422); }
        $user->addPaymentMethod($paymentMethodId);
        $pm = $user->findPaymentMethod($paymentMethodId);
        return $this->success($this->mapPaymentMethod($pm), 'Cashier card added');
    }

    public function createForPsw(array $data): array
    {
        $user = $this->user('psw-api');
        $paymentMethodId = $data['payment_method_id'] ?? null;
        if (!$paymentMethodId) { $this->fail('payment_method_id required', 422); }
        $user->addPaymentMethod($paymentMethodId);
        $pm = $user->findPaymentMethod($paymentMethodId);
        return $this->success($this->mapPaymentMethod($pm), 'Cashier card added');
    }

    public function updateForClient(string $paymentMethodId, array $data): array
    {
        $user = $this->user('api');
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) { $this->fail('Payment method not found', 404); }
        $updateParams = $this->extractBillingUpdate($data);
        if ($updateParams) {
            $client = new StripeClient(config('cashier.secret'));
            $client->paymentMethods->update($paymentMethodId, $updateParams);
            $pm = $user->findPaymentMethod($paymentMethodId); // refresh
        }
        return $this->success($this->mapPaymentMethod($pm), 'Cashier card updated');
    }

    public function updateForPsw(string $paymentMethodId, array $data): array
    {
        $user = $this->user('psw-api');
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) { $this->fail('Payment method not found', 404); }
        $updateParams = $this->extractBillingUpdate($data);
        if ($updateParams) {
            $client = new StripeClient(config('cashier.secret'));
            $client->paymentMethods->update($paymentMethodId, $updateParams);
            $pm = $user->findPaymentMethod($paymentMethodId);
        }
        return $this->success($this->mapPaymentMethod($pm), 'Cashier card updated');
    }

    public function deleteForClient(string $paymentMethodId): array
    {
        $user = $this->user('api');
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) { $this->fail('Payment method not found', 404); }
        $pm->delete();
        return $this->success(['payment_method_id' => $paymentMethodId], 'Cashier card deleted');
    }

    public function deleteForPsw(string $paymentMethodId): array
    {
        $user = $this->user('psw-api');
        $pm = $user->findPaymentMethod($paymentMethodId);
        if (!$pm) { $this->fail('Payment method not found', 404); }
        $pm->delete();
        return $this->success(['payment_method_id' => $paymentMethodId], 'Cashier card deleted');
    }

    protected function mapPaymentMethod($pm): array
    {
        return [
            'id' => $pm->id,
            'type' => $pm->type,
            'brand' => $pm->card->brand ?? null,
            'last4' => $pm->card->last4 ?? null,
            'exp_month' => $pm->card->exp_month ?? null,
            'exp_year' => $pm->card->exp_year ?? null,
            'country' => $pm->card->country ?? null,
            'funding' => $pm->card->funding ?? null,
            'billing_name' => $pm->billing_details->name ?? null,
            'billing_email' => $pm->billing_details->email ?? null,
            'billing_phone' => $pm->billing_details->phone ?? null,
        ];
    }

    protected function extractBillingUpdate(array $data): array
    {
        $billing = [];
        foreach (['name','email','phone'] as $field) {
            if (isset($data[$field])) { $billing[$field] = $data[$field]; }
        }
        $addressFields = [
            'line1' => 'address_line1',
            'line2' => 'address_line2',
            'city' => 'address_city',
            'state' => 'address_state',
            'postal_code' => 'address_postal_code',
            'country' => 'address_country',
        ];
        $address = [];
        foreach ($addressFields as $stripeKey => $inputKey) {
            if (isset($data[$inputKey])) { $address[$stripeKey] = $data[$inputKey]; }
        }
        $update = [];
        if ($billing) { $update['billing_details'] = $billing; }
        if ($address) {
            $update['billing_details'] = ($update['billing_details'] ?? []) + ['address' => $address];
        }
        return $update;
    }
}
