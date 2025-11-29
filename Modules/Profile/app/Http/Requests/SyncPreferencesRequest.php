<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * PSW must be authenticated via guard; controller/service will enforce.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'preferences' => 'required|array',
            'preferences.*' => 'integer|exists:preferences,id',
        ];
    }

    /**
     * Return sanitized data for the controller/service.
     *
     * @return array
     */
    public function getSanitized(): array
    {
        return [
            'preferences' => array_values(array_map('intval', $this->input('preferences', []))),
        ];
    }
}
