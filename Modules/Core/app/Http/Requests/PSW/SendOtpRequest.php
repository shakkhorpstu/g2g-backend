<?php

namespace Modules\Core\App\Http\Requests\PSW;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'phone' => 'required|string|min:6|max:20',
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
