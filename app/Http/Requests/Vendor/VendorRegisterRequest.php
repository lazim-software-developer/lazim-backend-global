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
            'name'      => 'required|min:3|max:30',
            'email'     => 'required|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
            'phone'    => 'required|string',
            'owner_association_id' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'The provided email is already registered.',
            'phone.unique' => 'The provided mobile number is already registered.',
        ];
    }
}
