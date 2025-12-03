<?php

namespace Modules\Core\Http\Requests\PSW;

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
            'psw_id' => 'required|integer',
            'otp_code' => ['required','string','regex:/^\\d{4,6}$/'],
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
