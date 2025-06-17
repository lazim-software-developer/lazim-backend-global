<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessCardFormsRequest extends FormRequest
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
            'card_type' => 'required|string|in:Parking,Lobby/Access Doors',
            'reason' => 'nullable|string',
            'parking_details' => 'nullable|json',
            'occupied_by' => 'nullable|in:Owner,Tenant,Vacant',
            'tenancy' => 'file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'vehicle_registration' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'title_deed' => 'file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'passport' => 'file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'emirate_of_registration' => 'required|integer|exists:emirate_of_registrations,id',
        ];
    }


    public function messages()
    {
        return [
            'tenancy.max' => 'The uploaded file for tenancy must be less than 2MB.',
            'vehicle_registration.max' => 'The uploaded file for vehicle registration must be less than 2MB.',
            'passport.max' => 'The uploaded image must be less than 2MB.',
            'title_deed.max' => 'The uploaded image must be less than 2MB.',
            'emirate_of_registration.required' => 'Select Emirate of Registration.',
            'emirate_of_registration.exists' => 'Invalid Emirate of Registration.',
        ];
    }
}
