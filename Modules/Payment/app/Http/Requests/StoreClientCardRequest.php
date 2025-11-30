<?php

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientCardRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'payment_method_id' => ['required','string'],
            'is_default' => ['sometimes','boolean'],
        ];
    }
}
