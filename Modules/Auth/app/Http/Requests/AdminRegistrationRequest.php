<?php

namespace Modules\Auth\Http\Requests;

class AdminRegistrationRequest extends BaseAuthRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only super admins can create new admins.
     */
    public function authorize(): bool
    {
        // Check if the authenticated user is a super admin
        $user = $this->user();
        
        if (!$user) {
            return false;
        }

        // Assuming you have a method to check if user is super admin
        return $user instanceof \App\Models\Admin && $user->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:admins,email'
            ],
            'password' => [
                'required',
                'string',
                'min:10',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'role' => [
                'required',
                'string',
                'in:admin,super_admin,moderator'
            ],
            'is_active' => [
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
            'name.regex' => 'The admin name may only contain letters and spaces.',
            'password.regex' => 'The admin password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
            'password.min' => 'The admin password must be at least 10 characters long.',
            'role.in' => 'The selected role is invalid.',
            'email.unique' => 'An admin with this email already exists.',
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Prevent creating multiple super admins (optional)
            if ($this->input('role') === 'super_admin') {
                $existingSuperAdmins = \App\Models\Admin::where('role', 'super_admin')->count();
                if ($existingSuperAdmins >= 1) {
                    // You can adjust this logic based on your business rules
                    // $validator->errors()->add('role', 'Only one super admin is allowed.');
                }
            }
        });
    }

    /**
     * Get the sanitized data for admin creation.
     */
    public function getSanitizedData(): array
    {
        return [
            'name' => trim($this->input('name')),
            'email' => strtolower(trim($this->input('email'))),
            'password' => $this->input('password'),
            'role' => $this->input('role'),
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}