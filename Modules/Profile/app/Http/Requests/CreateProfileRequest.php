<?php

namespace Modules\Profile\Http\Requests;

class CreateProfileRequest extends BaseProfileRequest
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
                'sometimes',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' // Strong password
            ],
            'role' => [
                'sometimes',
                'string',
                'in:user,admin'
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
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number and one special character.',
            'phone.regex' => 'The phone number format is invalid.',
            'role.in' => 'The role must be either user or admin.',
        ]);
    }
}