<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetRatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hourly_rate' => 'nullable|numeric|min:0',
            'include_driving_allowance' => 'required|boolean',
            'driving_allowance_per_km' => 'nullable|numeric|min:0',
        ];
    }
}
