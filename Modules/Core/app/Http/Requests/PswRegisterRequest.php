<?php

namespace Modules\Core\Http\Requests;

class PswRegisterRequest extends BaseCoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/' // Only letters and spaces
            ],
            'last_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:psws,email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'phone_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[1-9][\d]{0,15}$/'
            ],
            'gender' => [
                'sometimes',
                'nullable',
                'in:1,2,3'
            ],
            'address' => [
                'sometimes',
                'nullable',
                'array'
            ],
            'address.latitude' => [
                'sometimes',
                'required_with:address',
                'string',
                'max:32'
            ],
            'address.longitude' => [
                'sometimes',
                'required_with:address',
                'string',
                'max:32'
            ],
            'address.address_line' => [
                'sometimes',
                'required_with:address',
                'string',
                'max:500'
            ],
            'address.city' => [
                'sometimes',
                'required_with:address',
                'string',
                'max:255'
            ],
            'address.province' => [
                'sometimes',
                'required_with:address',
                'string',
                'max:255'
            ],
            'address.postal_code' => [
                'sometimes',
                'required_with:address',
                'string',
                'max:32'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name can only contain letters and spaces.',
            'last_name.regex' => 'Last name can only contain letters and spaces.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered as a PSW.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'phone_number.regex' => 'Please enter a valid phone number.',
            'gender.in' => 'Gender must be 1 (Male), 2 (Female), or 3 (Other).',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone_number' => 'phone number',
        ];
    }
}