<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $rules = [
            'name'                      => 'required',
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

        // If updating (edit route), ignore the current record in unique validation
        if ($this->route()->getName() === 'subcontractors.edit') {
            $subcontractor  = $this->route('subContract');
            $rules['email'] = ['required', Rule::unique('sub_contractors')->ignore($subcontractor->id)];
            $rules['phone'] = ['required', Rule::unique('sub_contractors')->ignore($subcontractor->id)];
        } else {
            $rules['email'] = 'required|unique:sub_contractors,email';
            $rules['phone'] = 'required|unique:sub_contractors,phone';
        }

        return $rules;
    }
}
