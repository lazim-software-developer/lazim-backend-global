<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorRegisterRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => 'required',
            'email'     => 'required|unique:users,email|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
            'mobile'    => 'required|string|unique:users,phone',
            'owner_association_id'     => 'required|integer'
        ];
    }
}
