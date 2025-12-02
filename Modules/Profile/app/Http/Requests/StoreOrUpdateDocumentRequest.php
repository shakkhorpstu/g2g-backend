<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateDocumentRequest extends FormRequest
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
            'document_type_id' => [
                'required',
                'integer',
                'exists:document_types,id'
            ],
            // Both sides or single side upload
            'front_file' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,pdf',
                'max:10240' // Max 10MB
            ],
            'back_file' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,pdf',
                'max:10240'
            ],
            'metadata' => [
                'nullable',
                'array'
            ],
            'metadata.expiry_date' => [
                'nullable',
                'date',
                'after:today'
            ],
            'metadata.issue_date' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
            'metadata.document_number' => [
                'nullable',
                'string',
                'max:255'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'document_type_id.required' => 'Document type is required',
            'document_type_id.exists' => 'Invalid document type selected',
            'front_file.mimes' => 'Front document must be a valid image (JPEG, PNG, GIF) or PDF file',
            'front_file.max' => 'Front document file size cannot exceed 10MB',
            'back_file.mimes' => 'Back document must be a valid image (JPEG, PNG, GIF) or PDF file',
            'back_file.max' => 'Back document file size cannot exceed 10MB',
            'metadata.expiry_date.after' => 'Expiry date must be in the future',
            'metadata.issue_date.before_or_equal' => 'Issue date cannot be in the future',
        ];
    }

}
