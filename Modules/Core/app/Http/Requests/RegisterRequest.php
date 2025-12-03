<?php

namespace Modules\Core\Http\Requests;

class RegisterRequest extends BaseCoreRequest
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
                'nullable',
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
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/' // Strong password
            ],
            'gender' => [
                'sometimes',
                'numeric',
                'in:1,2,3'
            ],
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^[\+]?[\d\-\s\(\)]+$/' // Phone number format
            ],
            'address' => [
                'sometimes',
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
     * Get sanitized and validated data
     */
    public function getSanitizedData(): array
    {
        $data = $this->validated();
        
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }
        
        // Trim string fields
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        
        if (isset($data['phone'])) {
            $data['phone'] = trim($data['phone']);
        }
        
        if (isset($data['address']) && is_array($data['address'])) {
            $addr = $data['address'];
            $clean = [];
            if (isset($addr['latitude'])) {
                $clean['latitude'] = trim((string) $addr['latitude']);
            }
            if (isset($addr['longitude'])) {
                $clean['longitude'] = trim((string) $addr['longitude']);
            }
            if (isset($addr['address_line'])) {
                $clean['address_line'] = trim($addr['address_line']);
            }
            if (isset($addr['city'])) {
                $clean['city'] = trim($addr['city']);
            }
            if (isset($addr['province'])) {
                $clean['province'] = trim($addr['province']);
            }
            if (isset($addr['postal_code'])) {
                $clean['postal_code'] = trim((string) $addr['postal_code']);
            }

            $data['address'] = $clean;
        }
        
        if (isset($data['bio'])) {
            $data['bio'] = trim($data['bio']);
        }
        
        return $data;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.regex' => 'Name can only contain letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'role.in' => 'Role must be either user or admin.',
            'phone.regex' => 'Please provide a valid phone number.',
        ]);
    }
}