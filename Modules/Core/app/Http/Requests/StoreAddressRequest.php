<?php

namespace Modules\Core\Http\Requests;

use App\Shared\Enums\AddressLabel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // 'label' => ['required', 'string', Rule::enum(AddressLabel::class)],
            'label' => ['required', 'string'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'label.required' => 'Address label is required',
            'label.enum' => 'Invalid address label',
            'address_line.required' => 'Address line is required',
            'address_line.max' => 'Address line cannot exceed 500 characters',
            'city.required' => 'City is required',
            'province.required' => 'Province is required',
            'postal_code.required' => 'Postal code is required',
            'country_id.required' => 'Country is required',
            'country_id.exists' => 'Invalid country selected',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
        ];
    }
}