<?php

namespace Modules\Core\Http\Requests;

class LoginRequest extends BaseCoreRequest
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
            'remember' => [
                'sometimes',
                'boolean'
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
}