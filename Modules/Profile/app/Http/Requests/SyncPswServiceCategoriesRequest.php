<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPswServiceCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_ids' => 'required|array',
            'service_category_ids.*' => 'required|integer|exists:service_categories,id',
            'has_own_vehicle' => 'sometimes|boolean',
        ];
    }

    public function getSanitized(): array
    {
        return [
            'service_category_ids' => array_values(array_map('intval', $this->input('service_category_ids', []))),
            'has_own_vehicle' => $this->has('has_own_vehicle') ? (bool) $this->input('has_own_vehicle') : null,
        ];
    }
}
