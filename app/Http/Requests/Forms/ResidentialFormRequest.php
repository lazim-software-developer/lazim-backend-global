<?php

namespace App\Http\Requests\Forms;

use App\Models\OwnerAssociation;
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
            'number_of_adults' => 'required|integer',
            'number_of_children' => 'required|integer',
            'office_number' => 'nullable|string',
            'trn_number' => 'nullable|string',
            'emirates_id' => 'required|string',
            'title_deed_number' => 'string',
            'emergency_contact' => 'required|json',
            'passport_number' => $this->selectType !== OwnerAssociation::OA_TYPE ? 'required|string' : 'nullable|string',
            'passport_expires_on' => $this->selectType !== OwnerAssociation::OA_TYPE ? 'required|date' : 'nullable|date',
            'file_passport_url' => $this->selectType !== OwnerAssociation::OA_TYPE ? 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048' : 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'emirates_expires_on' => $this->selectType !== OwnerAssociation::OA_TYPE ? 'required|date' : 'nullable|date',
            'file_emirates_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'file_title_deed_url' => 'file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'file_tenancy_contract' => 'file|mimes:pdf,jpeg,png,doc,docx|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $UserType = auth()->user()->role->name;
            if ($UserType == 'Owner') {
                if (!$this->hasFile('file_title_deed_url')) {
                    $validator->errors()->add('title_deed_url', 'Upload Title Deed File.');
                }
                // if (!$this->hasFile('title_deed_number')) {
                //     $validator->errors()->add('title_deed_number', 'Please enter title deed number.');
                // }
            }

            if ($UserType == 'Tenant') {
                if (!$this->hasFile('file_tenancy_contract')) {
                    $validator->errors()->add('tenancy_contract', 'Upload Tenancy Contract / Ejari File.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'file_passport_url.max' => 'The uploaded image must be less than 2MB.',
            'file_emirates_url.max' => 'The uploaded image must be less than 2MB.',
            'file_title_deed_url.max' => 'The uploaded image must be less than 2MB.',
            'file_tenancy_contract.max' => 'The uploaded image must be less than 2MB.',
        ];
    }
}
