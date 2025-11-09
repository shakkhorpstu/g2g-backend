<?php

namespace Modules\Core\Http\Requests;

class AdminLoginRequest extends BaseCoreRequest
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
                'min:1'
            ],
            'additional_verification' => [
                'sometimes',
                'string'
            ]
        ];
    }

    /**
     * Get sanitized and validated data
     */
    public function getSanitizedData(): array
    {
        $data = $this->validated();
        
        // Sanitize email
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }
        
        return $data;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.required' => 'Admin email is required.',
            'password.required' => 'Admin password is required.',
        ]);
    }
}