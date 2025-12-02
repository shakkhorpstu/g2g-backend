<?php

namespace Modules\Core\Http\Requests;

class VerifyAccountRequest extends BaseCoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255'
            ],
            'otp_code' => [
                'required',
                'string',
                'size:4'
            ],
            'identifier' => [
                'required',
                'string'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'otp_code.required' => 'OTP code is required.',
            'otp_code.size' => 'OTP code must be 6 digits.'
        ]);
    }
}
