<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends BaseProfileRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = Auth::id();
        
        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/' // Only letters and spaces
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                "unique:users,email,{$userId}" // Exclude current user's email
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/' // Phone number format
            ],
            'address' => [
                'sometimes',
                'nullable',
                'string',
                'max:500'
            ],
            'bio' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.regex' => 'The name may only contain letters and spaces.',
            'phone.regex' => 'The phone number format is invalid.',
        ]);
    }
}