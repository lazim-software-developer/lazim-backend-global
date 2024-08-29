<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorFormRequest extends FormRequest
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
            'work_type' => 'required|in:major,minor',
            'work_name' => 'required|min:2|max:50',
            'documents.vendor_contractor_trade_license' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.vendor_contractor_all_risk_policy' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.third_party_liability_insurance' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.undertaking_letter_for_waterproofing' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.noc_from_owner' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.civil_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.electrical_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.load_schedule' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.hvac_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.plumbing_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.fire_fighting_fire_alarm_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.architectural_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.structural_drawings' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.waterproofing' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
            'documents.others' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
        ];
    }
}
