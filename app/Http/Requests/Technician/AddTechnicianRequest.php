<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class AddTechnicianRequest extends FormRequest
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
            'name'          =>  'required|string|max:100',
            'email'         =>  'required|email|unique:users,email',
            'phone'         =>  'required|string|unique:users,phone',
            'position'      =>  'required|string|max:100',
            'service_id'    =>  'required|integer|exists:services,id',
        ];
    }
}
