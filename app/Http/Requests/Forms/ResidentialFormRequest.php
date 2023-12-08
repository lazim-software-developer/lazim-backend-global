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
            'unit_occupied_by' => 'required|in:Owner,Tenant,Vacant',
            'name' => 'required|string',
            'building_id' => 'required|exists:buildings,id',
            'flat_id' => 'required|exists:flats,id',
            'passport_number' => 'required|string',
            'number_of_adults' => 'required|integer',
            'number_of_children' => 'required|integer',
            'office_number' => 'nullable|string',
            'trn_number' => 'nullable|string',
            'passport_expires_on' => 'required|date',
            'emirates_id' => 'required|string',
            'emirates_expires_on' => 'required|date',
            'title_deed_number' => 'required_if:unit_occupied_by,Owner|string',
            'emergency_contact' => 'required|json',
            'passport_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'emirates_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'title_deed_url' => 'required_if:unit_occupied_by,Owner|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'tenancy_contract' => 'required_if:unit_occupied_by,Tenant|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $UserType = auth()->user()->role->name;
            if ($UserType == 'Owner') {
                if (!$this->hasFile('title_deed_url')) {
                    $validator->errors()->add('title_deed_url', 'Upload Title Deed File.');
                }
                if (!$this->hasFile('title_deed_number') ) {
                    $validator->errors()->add('title_deed_number', 'Please enter title deed number.');
                }
            }

            if ($UserType == 'Tenant') {
                if (!$this->hasFile('tenancy_contract')) {
                    $validator->errors()->add('tenancy_contract', 'Upload Tenancy Contract / Ejari File.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'passport_url.max' => 'The uploaded image must be less than 2MB.',
            'emirates_url.max' => 'The uploaded image must be less than 2MB.',
            'title_deed_url.max' => 'The uploaded image must be less than 2MB.',
            'tenancy_contract.max' => 'The uploaded image must be less than 2MB.',
        ];
    }
}
