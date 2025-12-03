<?php

namespace Modules\Profile\Http\Requests;

class VerifyEmailChangeRequest extends BaseProfileRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required','string','in:email,phone'],
            'new_value' => [
                'required',
                'string',
                'max:255',
                function($attribute,$value,$fail){
                    if(request('type')==='email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Please provide a valid email address.');
                    }
                    if(request('type')==='phone' && !preg_match('/^[\+]?[0-9\s\-\(\)]+$/',$value)) {
                        $fail('Please provide a valid phone number.');
                    }
                }
            ],
            'otp_code' => [
                'required',
                'string',
                'regex:/^\\d{4,6}$/'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'type.required' => 'Verification type is required.',
            'type.in' => 'Type must be either email or phone.',
            'new_value.required' => 'New value is required.',
            'otp_code.required' => 'OTP code is required.',
            'otp_code.regex' => 'OTP code must be 4 to 6 digits.'
        ]);
    }
}
