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
            'sale_price' => 'required|numeric',
            'cooling_bill_paid' => 'required|boolean',
            'service_charge_paid' => 'required|boolean',
            'noc_fee_paid' => 'required|boolean',
            'service_charge_paid_till' => 'required|date',
            'cooling_receipt' => 'required',
            'cooling_soa' => 'required',
            'cooling_clearance' => 'required',
            'payment_receipt' => 'required',
            'contacts.*.type' => 'required|in:buyer,seller',
            'contacts.*.emirates_id' => 'nullable|string|max:20',
            'contacts.*.passport_number' => 'nullable|string|max:20',
            'contacts.*.visa_number' => 'nullable|string|max:20',
            'contacts.*.emirates_document_url' => 'required',
            'contacts.*.visa_document_url' => 'required',
            'contacts.*.passport_document_url' => 'required',
            'signing_authority_email' => 'required',
            'signing_authority_phone' => 'required',
            'signing_authority_name' => 'required',
        ];
    }
}
