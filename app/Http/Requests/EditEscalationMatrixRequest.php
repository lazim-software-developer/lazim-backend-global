<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditEscalationMatrixRequest extends FormRequest
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
            'email'             =>  'required|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/|unique:vendor_escalation_matrix,email,'.$this->route('escalationmatrix')->id,
            'phone'             =>  'required|string|unique:vendor_escalation_matrix,phone,'.$this->route('escalationmatrix')->id,
            'position'          =>  'required|string|max:100',
            'escalation_level'  =>  'required|integer',
        ];
    }
}
