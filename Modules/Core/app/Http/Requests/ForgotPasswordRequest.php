<?php

namespace Modules\Core\Http\Requests;

class ForgotPasswordRequest extends BaseCoreRequest
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
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
        ]);
    }
}
