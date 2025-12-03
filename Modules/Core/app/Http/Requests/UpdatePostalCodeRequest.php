<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostalCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'postal_code' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.required' => 'Postal code is required.',
            'postal_code.string' => 'Postal code must be a valid string.',
            'postal_code.max' => 'Postal code cannot exceed 5 characters.',
        ];
    }
}
