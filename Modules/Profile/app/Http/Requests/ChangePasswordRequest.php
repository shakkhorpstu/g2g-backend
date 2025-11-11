<?php

namespace Modules\Profile\Http\Requests;

class ChangePasswordRequest extends BaseProfileRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'min:8'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required',
            'current_password.min' => 'Current password must be at least 8 characters',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.confirmed' => 'New password confirmation does not match',
            'new_password_confirmation.required' => 'New password confirmation is required',
            'new_password_confirmation.min' => 'New password confirmation must be at least 8 characters',
        ];
    }

    /**
     * Get sanitized data for processing
     */
    public function getSanitizedData(): array
    {
        return [
            'current_password' => $this->input('current_password'),
            'new_password' => $this->input('new_password'),
        ];
    }
}