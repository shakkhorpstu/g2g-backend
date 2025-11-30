<?php

namespace Modules\Core\Http\Requests;

class ServiceCategoryStoreRequest extends BaseCoreRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'subtitle' => ['required','string','max:255'],
            'price' => ['required','numeric','min:0'],
            'base_fare' => ['required','numeric','min:0'],
            'ride_charge' => ['nullable','numeric','min:0'],
            'time_charge' => ['required','numeric','min:0'],
            'platform_fee' => ['required','numeric','min:0'],
        ];
    }
}