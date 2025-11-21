<?php

namespace Modules\Core\Http\Requests;

class ServiceCategoryUpdateRequest extends BaseCoreRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes','required','string','max:255'],
            'subtitle' => ['sometimes','required','string','max:255'],
            'price' => ['sometimes','required','numeric','min:0'],
            'base_fare' => ['sometimes','required','numeric','min:0'],
            'ride_charge' => ['sometimes','nullable','numeric','min:0'],
            'time_charge' => ['sometimes','required','numeric','min:0'],
            'platform_fee' => ['sometimes','required','numeric','min:0'],
        ];
    }
}