<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => 'required|string|in:email,phone',
            'otp_code' => ['required','string','regex:/^\d{4,6}$/'],
        ];
    }

    public function getSanitizedData(): array
    {
        return $this->validated();
    }
}
