<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetLanguageRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // Accept either a single `language` or an array `languages`.
            'language' => ['sometimes', 'string', 'max:10'],
            'languages' => ['sometimes', 'array', 'min:1'],
            'languages.*' => ['string', 'max:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'language.string' => 'Language must be a string',
            'language.max' => 'Language is too long',
            'languages.array' => 'Languages must be an array',
            'languages.min' => 'At least one language is required',
            'languages.*.string' => 'Each language must be a string',
            'languages.*.max' => 'Each language value is too long',
        ];
    }

    /**
     * Normalize input so controllers/services always receive `languages` array.
     */
    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if ($this->filled('language') && !$this->filled('languages')) {
            $this->merge([ 'languages' => [$this->input('language')] ]);
        }
    }
}
