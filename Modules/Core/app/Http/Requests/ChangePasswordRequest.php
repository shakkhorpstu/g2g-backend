<?php

namespace Modules\Core\Http\Requests;

class ChangePasswordRequest extends BaseCoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string'
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', // Strong password
                'different:current_password'
            ]
        ];
    }

    /**
     * Get sanitized and validated data
     */
    public function getSanitizedData(): array
    {
        return $this->validated();
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'current_password.required' => 'Please enter your current password.',
            'new_password.regex' => 'New password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'new_password.different' => 'New password must be different from current password.',
        ]);
    }
}