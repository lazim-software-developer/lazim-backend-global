<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class SaleNocRequest extends FormRequest
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
            'building_id' => 'required|exists:buildings,id',
            'flat_id' => 'required|exists:flats,id',
            'unit_occupied_by' => 'required|in:Owner,Tenant,Vacant',
            'applicant' => 'required|string',
            'unit_area' => 'required|string',
            'sale_price' => 'nullable|numeric',
            // 'cooling_bill_paid' => 'required|boolean',
            // 'service_charge_paid' => 'required|boolean',
            // 'noc_fee_paid' => 'required|boolean',
            'service_charge_paid_till' => 'required|date',
            'signing_authority_email' => 'required',
            'signing_authority_phone' => 'required',
            'signing_authority_name' => 'required',

            //common documents
            'cooling_receipt' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'cooling_soa' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'cooling_clearance' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'payment_receipt' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',

            //details of seller's/buyer's
            'contacts.*.type' => 'required|in:buyer,seller',
            'contacts.*.emirates_id' => 'nullable|string|max:20',
            'contacts.*.first_name' => 'required|string',
            'contacts.*.email' => 'required|string',
            'contacts.*.mobile' => 'required|string|max:20',
            'contacts.*.agent_email' => 'nullable|string',
            'contacts.*.agent_phone' => 'nullable|string|max:20',
            'contacts.*.passport_number' => 'required_if:contacts.*.type,seller|string|max:20',
            'contacts.*.visa_number' => 'nullable|string|max:20',

            //documents for seller's/buyer's
            'contacts.*.emirates_document_url' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'contacts.*.poa_document' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'contacts.*.visa_document_url' => 'nullable|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'contacts.*.passport_document_url' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'contacts.*.title_deed' => 'required_if:contacts.*.type,seller|file|mimes:pdf,jpeg,png,doc,docx|max:2048',

        ];
    }

    public function messages()
    {
        return [
            'contacts.*.type' => 'Please enter a valid Type.',
            "email"=> "Please enter a valid email.",
            "mobile"=> "Please enter a valid phone number.",
            "contacts.*.emirates_document_url"=> 'Please upload an emirates document',
            "contacts.*.visa_document_url"=> 'Please upload a visa document',
            "contacts.*.passport_document_url"=> 'Please upload a passport document',
            "contacts.*.title_deed"=> 'Please upload a title deed document'
        ];
    }
}
