<?php

namespace Modules\Core\Http\Requests;

class ResetPasswordRequest extends BaseCoreRequest
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
                'regex:/^\\d{4,6}$/'
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$/'
            ],
            'new_password_confirmation' => [
                'required'
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
            'otp_code.regex' => 'OTP code must be 4 to 6 digits.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'Password must be at least 8 characters.',
            'new_password.confirmed' => 'Password confirmation does not match.',
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'new_password_confirmation.required' => 'Password confirmation is required.'
        ]);
    }
}
