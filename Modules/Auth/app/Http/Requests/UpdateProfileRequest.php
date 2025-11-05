<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Support\Facades\Hash;

class UpdateProfileRequest extends BaseAuthRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->user()->id;
        $userTable = $this->user() instanceof \App\Models\Admin ? 'admins' : 'users';

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                "unique:{$userTable},email,{$userId}"
            ],
            'current_password' => [
                'required_with:password',
                'string'
            ],
            'password' => [
                'sometimes',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
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
            'password.regex' => 'The new password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
            'current_password.required_with' => 'Current password is required when updating password.',
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Verify current password if password update is requested
            if ($this->filled('password') && $this->filled('current_password')) {
                if (!Hash::check($this->input('current_password'), $this->user()->password)) {
                    $validator->errors()->add('current_password', 'The current password is incorrect.');
                }
            }
        });
    }

    /**
     * Get the sanitized data for profile update.
     */
    public function getSanitizedData(): array
    {
        $data = [];

        if ($this->filled('name')) {
            $data['name'] = trim($this->input('name'));
        }

        if ($this->filled('email')) {
            $data['email'] = strtolower(trim($this->input('email')));
        }

        if ($this->filled('password')) {
            $data['password'] = $this->input('password');
        }

        return $data;
    }
}