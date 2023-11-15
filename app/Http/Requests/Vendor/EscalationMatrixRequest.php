<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class EscalationMatrixRequest extends FormRequest
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
            'name'              =>  'required|string|max:100',
            'email'             =>  'required|unique:vendor_escalation_matrix,email|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
            'phone'             =>  'required|string|unique:vendor_escalation_matrix,phone',
            'position'          =>  'required|string|max:100',
            'escalation_level'  =>  'required|integer',
        ];
    }
}
