<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bio' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get sanitized data
     */
    public function getSanitizedData(): array
    {
        return [
            'bio' => $this->input('bio'),
        ];
    }
}
