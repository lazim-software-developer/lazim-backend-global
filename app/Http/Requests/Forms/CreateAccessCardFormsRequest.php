<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccessCardFormsRequest extends FormRequest
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
            'building_id' => 'required|integer',
            'flat_id' => 'required|integer',
            'card_type' => 'required|string',
            'reason' => 'nullable|string',
            'parking_details' => 'nullable|json',
            'occupied_by' => 'required',
            'tenancy' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'vehicle_registration' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',

        ];
    }

    public function messages()
    {
        return [
            'tenancy' => 'The uploaded image must be less than 2MB.',
            'vehicle_registration' => 'The uploaded image must be less than 2MB.',
        ];
    }
}
