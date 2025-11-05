<?php

namespace Modules\Auth\Http\Requests;

class UserRegistrationRequest extends BaseAuthRequest
{
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
                'regex:/^[a-zA-Z\s]+$/' // Only letters and spaces
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' // Strong password
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
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
            'password.min' => 'The password must be at least 8 characters long.',
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic can go here
            if ($this->input('name') && strlen($this->input('name')) < 2) {
                $validator->errors()->add('name', 'Name is too short.');
            }
        });
    }

    /**
     * Get the sanitized data from the request.
     */
    public function getSanitizedData(): array
    {
        return [
            'name' => trim($this->input('name')),
            'email' => strtolower(trim($this->input('email'))),
            'password' => $this->input('password'),
        ];
    }
}