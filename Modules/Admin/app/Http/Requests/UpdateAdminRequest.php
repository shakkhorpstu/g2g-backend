<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:191',
            'email' => "sometimes|required|email|max:191|unique:admins,email,{$adminId}",
            'password' => 'nullable|string|min:6|max:191',
        ];
    }
}
