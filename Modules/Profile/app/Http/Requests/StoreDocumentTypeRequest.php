<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'unique:document_types,key',
                'regex:/^[a-z0-9_]+$/' // Lowercase, numbers, underscores only
            ],
            'title' => [
                'required',
                'string',
                'max:255'
            ],
            'icon' => [
                'nullable',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'both_sided' => [
                'sometimes',
                'boolean'
            ],
            'both_sided_required' => [
                'sometimes',
                'boolean'
            ],
            'front_side_title' => [
                'nullable',
                'string',
                'max:255'
            ],
            'back_side_title' => [
                'nullable',
                'string',
                'max:255'
            ],
            'allowed_mime' => [
                'nullable',
                'array'
            ],
            'allowed_mime.*' => [
                'string',
                'in:image/jpeg,image/jpg,image/png,image/gif,application/pdf'
            ],
            'max_size_kb' => [
                'nullable',
                'integer',
                'min:100',
                'max:10240' // Max 10MB
            ],
            'active' => [
                'sometimes',
                'boolean'
            ],
            'is_required' => [
                'sometimes',
                'boolean'
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Document type key is required',
            'key.unique' => 'This document type key already exists',
            'key.regex' => 'Key must contain only lowercase letters, numbers, and underscores',
            'title.required' => 'Document type title is required',
            'allowed_mime.*.in' => 'Allowed mime type must be a valid image or PDF format',
            'max_size_kb.min' => 'Maximum file size must be at least 100KB',
            'max_size_kb.max' => 'Maximum file size cannot exceed 10MB',
        ];
    }
}
