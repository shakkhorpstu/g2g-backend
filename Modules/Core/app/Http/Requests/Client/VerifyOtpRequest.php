<?php

namespace Modules\Core\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'otp_code' => 'required|string|size:6',
        ];
    }

    /**
     * Return sanitized data for services/controllers
     */
    public function getSanitizedData(): array
    {
        return $this->validated();
    }
}
