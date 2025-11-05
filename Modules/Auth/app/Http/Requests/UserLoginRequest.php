<?php

namespace Modules\Auth\Http\Requests;

class UserLoginRequest extends BaseAuthRequest
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
            'password' => [
                'required',
                'string',
                'min:6',
                'max:255'
            ],
            'remember_me' => [
                'sometimes',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.email' => 'Please enter a valid email address.',
            'password.min' => 'Password must be at least 6 characters long.',
        ]);
    }

    /**
     * Get the sanitized credentials for authentication.
     */
    public function getCredentials(): array
    {
        return [
            'email' => strtolower(trim($this->input('email'))),
            'password' => $this->input('password'),
        ];
    }

    /**
     * Check if remember me is requested.
     */
    public function shouldRemember(): bool
    {
        return $this->boolean('remember_me');
    }

    /**
     * Get sanitized data for login.
     * Returns the same data as getCredentials() for consistency.
     */
    public function getSanitizedData(): array
    {
        return $this->getCredentials();
    }
}