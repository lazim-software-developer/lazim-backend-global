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
            'cooling_receipt' => 'nullable',
            'cooling_soa' => 'nullable',
            'cooling_clearance' => 'nullable',
            'payment_receipt' => 'nullable',

            //details of seller's/buyer's
            'contacts.*.type' => 'required|in:buyer,seller',
            'contacts.*.first_name' => 'required|string',
            'contacts.*.email' => 'required|string',
            'contacts.*.mobile' => 'required|string|max:20',
            'contacts.*.agent_email' => 'nullable|string',
            'contacts.*.agent_phone' => 'nullable|string|max:20',
            'contacts.*.passport_number' => 'required|string|max:20',
            'contacts.*.visa_number' => 'nullable|string|max:20',
            'contacts.*.emirates_id' => 'nullable|string|max:20',

            //documents for seller's/buyer's
            'contacts.*.emirates_document_url' => 'nullable',
            'contacts.*.poa_document' => 'nullable',
            'contacts.*.visa_document_url' => 'nullable',
            'contacts.*.passport_document_url' => 'required',
            'contacts.*.title_deed' => 'required_if:contacts.type,seller',

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
