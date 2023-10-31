<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class ResidentialFormRequest extends FormRequest
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
            'unit_occupied_by' => 'required|string',
            'name' => 'required|string',
            'building_id' => 'required|exists:buildings,id',
            'flat_id' => 'required|exists:flats,id',
            'passport_number' => 'nullable|string',
            'number_of_adults' => 'required|integer',
            'number_of_children' => 'required|integer',
            'office_number' => 'nullable|string',
            'trn_number' => 'nullable|string',
            'passport_expires_on' => 'nullable|date',
            'emirates_id' => 'nullable|string',
            'emirates_expires_on' => 'nullable|date',
            'title_deed_number' => 'nullable|string',
            'emergency_contact' => 'required|json',
            'passport_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'emirates_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'title_deed_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
        ];
    }
}
