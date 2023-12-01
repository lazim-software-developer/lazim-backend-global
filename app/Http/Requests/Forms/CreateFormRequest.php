<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormRequest extends FormRequest
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
            'type' =>'required',
            'moving_date'=> 'required|after_or_equal:today',
            'moving_time'=> 'required|date|after_or_equal:now',
            'time_preference'=> 'required',
            'handover_acceptance' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'receipt_charges' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'contract' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'title_deed' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'passport' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'dewa' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'cooling_registration' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'gas_registration' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'vehicle_registration' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'movers_license' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'movers_liability' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'building_id' => "Please select a building",
            'flat_id' => "Please select a flat",
            'type.required' =>'Type is required.',
            'handover_acceptance.max' => 'The uploaded image must be less than 2MB.' ,
            'receipt_charges.max' => 'The uploaded image must be less than 2MB.',
            'contract.max' => 'The uploaded image must be less than 2MB.',
            'title_deed.max' => 'The uploaded image must be less than 2MB.',
            'passport.max' => 'The uploaded image must be less than 2MB.',
            'dewa.max' => 'The uploaded image must be less than 2MB.',
            'cooling_registration.max' => 'The uploaded image must be less than 2MB.',
            'gas_registration.max' => 'The uploaded image must be less than 2MB.',
            'vehicle_registration.max' => 'The uploaded image must be less than 2MB.',
            'movers_license.max' => 'The uploaded image must be less than 2MB.',
            'movers_liability.max' => 'The uploaded image must be less than 2MB.',
        ];
    }
}
