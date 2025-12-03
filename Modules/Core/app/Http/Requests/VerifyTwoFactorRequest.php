<?php

namespace Modules\Core\Http\Requests;

use Modules\Core\Http\Requests\BaseCoreRequest;

class VerifyTwoFactorRequest extends BaseCoreRequest
{
    /**
     * Validation rules for verifying two-factor OTP
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'identifier' => 'required|string',
            'otp_code' => ['required','string','regex:/^\\d{4,6}$/'],
        ];
    }

    /**
     * Return sanitized data for service consumption
     */
    public function getSanitizedData(): array
    {
        return $this->validated();
    }
}
