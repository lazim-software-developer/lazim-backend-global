<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubContractorsRequest extends FormRequest
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
            'name'             => 'required',
            'email'            => 'required',
            'phone'            => 'required',
            'company_name'     => 'required',
            'trn_no'           => 'required|max_digits:15',
            'service_provided' => 'required',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
            'trade_licence'    => 'sometimes|file|mimes:pdf|max:2048',
            'contract_paper'   => 'sometimes|file|mimes:png,jpg,pdf|max:2048',
            'agreement_letter' => 'sometimes|file|mimes:png,jpg,pdf|max:2048',
            'additional_doc'   => 'sometimes|file|mimes:png,jpg,pdf|max:2048',
            'active'           => 'sometimes|in:0,1'
        ];
    }
}
