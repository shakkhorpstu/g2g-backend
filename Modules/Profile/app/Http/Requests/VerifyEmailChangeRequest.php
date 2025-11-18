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
            'new_email' => [
                'required',
                'string',
                'email',
                'max:255'
            ],
            'otp_code' => [
                'required',
                'string',
                'size:6'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'new_email.required' => 'New email address is required.',
            'new_email.email' => 'Please provide a valid email address.',
            'otp_code.required' => 'OTP code is required.',
            'otp_code.size' => 'OTP code must be 6 digits.'
        ]);
    }
}
