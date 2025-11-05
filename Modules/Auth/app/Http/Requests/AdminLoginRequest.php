<?php

namespace Modules\Auth\Http\Requests;

class AdminLoginRequest extends BaseAuthRequest
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
                'min:8',
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
            'email.email' => 'Please enter a valid admin email address.',
            'password.min' => 'Admin password must be at least 8 characters long.',
        ]);
    }

    /**
     * Configure the validator instance for admin-specific validation.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if email belongs to admin domain (optional)
            $email = $this->input('email');
            if ($email && !$this->isValidAdminEmail($email)) {
                // You can enable this if you want domain restrictions
                // $validator->errors()->add('email', 'Invalid admin email domain.');
            }
        });
    }

    /**
     * Get the sanitized credentials for admin authentication.
     */
    public function getCredentials(): array
    {
        return [
            'email' => strtolower(trim($this->input('email'))),
            'password' => $this->input('password'),
            'is_active' => true, // Only allow active admins to login
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
     * Validate admin email domain (optional - customize as needed).
     */
    private function isValidAdminEmail(string $email): bool
    {
        // Add your admin email domain validation logic here
        // Example: only allow emails from specific domains
        $allowedDomains = config('auth.admin_allowed_domains', []);
        
        if (empty($allowedDomains)) {
            return true; // No domain restrictions
        }

        $domain = substr(strrchr($email, "@"), 1);
        return in_array($domain, $allowedDomains);
    }
}