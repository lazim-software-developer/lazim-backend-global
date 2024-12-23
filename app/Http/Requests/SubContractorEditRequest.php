<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubContractorEditRequest extends FormRequest
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
            'name'                      => 'required',
            'email'                     => 'required|unique:sub_contractors,email,' . $this->route('subContract')->id,
            'phone'                     => 'required|unique:sub_contractors,phone,' . $this->route('subContract')->id,
            'company_name'              => 'required',
            'trn_no'                    => 'required|max_digits:15',
            'services'                  => 'required',
            'start_date'                => 'required|date',
            'end_date'                  => 'required|date',
            'trade_licence'             => 'sometimes|file|mimes:pdf|max:2048',
            'trade_licence_expiry_date' => 'required|date',
            'contract_paper'            => 'sometimes|file|mimes:png,jpg,pdf|max:2048',
            'agreement_letter'          => 'sometimes|file|mimes:png,jpg,pdf|max:2048',
            'additional_doc'            => 'sometimes|file|mimes:png,jpg,pdf|max:2048',
            'active'                    => 'sometimes|in:0,1',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'The phone number field is required.',
            'phone.unique' => 'This phone number is already registered.',
            'company_name.required' => 'The company name field is required.',
            'trn_no.required' => 'The TRN number field is required.',
            'trn_no.max_digits' => 'The TRN number must not exceed 15 digits.',
            'services.required' => 'Please select at least one service.',
            'start_date.required' => 'The start date field is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.required' => 'The end date field is required.',
            'end_date.date' => 'Please enter a valid end date.',
            'trade_licence.file' => 'The trade license must be a file.',
            'trade_licence.mimes' => 'The trade license must be a PDF file.',
            'trade_licence.max' => 'The trade license file size must not exceed 2MB.',
            'trade_licence_expiry_date.required' => 'The trade license expiry date is required.',
            'trade_licence_expiry_date.date' => 'Please enter a valid trade license expiry date.',
            'contract_paper.file' => 'The contract paper must be a file.',
            'contract_paper.mimes' => 'The contract paper must be a PNG, JPG, or PDF file.',
            'contract_paper.max' => 'The contract paper file size must not exceed 2MB.',
            'agreement_letter.file' => 'The agreement letter must be a file.',
            'agreement_letter.mimes' => 'The agreement letter must be a PNG, JPG, or PDF file.',
            'agreement_letter.max' => 'The agreement letter file size must not exceed 2MB.',
            'additional_doc.file' => 'The additional document must be a file.',
            'additional_doc.mimes' => 'The additional document must be a PNG, JPG, or PDF file.',
            'additional_doc.max' => 'The additional document file size must not exceed 2MB.',
            'active.in' => 'The active field must be either 0 or 1.',
        ];
    }
}
