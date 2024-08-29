<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateGuestRequest extends FormRequest
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
            'visitor_names' => 'required',
            'visitor_passports' => 'required',
            'visa_validity_date' => 'nullable|date',
            'stay_duration' => 'required',
            'original_passport' => 'nullable',
            'access_card_holder'=> 'nullable',
            'guest_registration' => 'nullable',
            'building_id' => 'required|integer',
            'flat_id' => 'required|integer',
            'start_date' => 'required|date',
            'number_of_visitors' => 'required|integer',
            'type' => 'required',
            'holiday_home_name' => 'nullable',
            'emergency_contact' => 'nullable',
            'end_date' => 'required|date', //|after:start_date
            'image' => 'required|file|mimes:pdf,jpg,jpeg,png,doc|max:2048',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'image.max' => 'The uploaded image must be less than 2MB.',
            'files.*' => 'Invalid file type. Allowed file types are: pdf, jpg, jpeg, png, doc.',
            // 'files.max' => 'The uploaded image must be less than 2MB.',
        ];
    }
}
