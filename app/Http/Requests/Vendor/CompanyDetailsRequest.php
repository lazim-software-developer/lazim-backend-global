<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CompanyDetailsRequest extends FormRequest
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
        $vendorId = $this->vendor_id ?? '';
        return [
            'owner_id'        =>    'required|integer|exists:users,id',
            'address_line_1'  =>    'required|string',
            'address_line_2'  =>    'nullable|string',
            'landline_number' =>    'required|string|unique:vendors,landline_number,' . $vendorId,
            'website'         =>    'nullable',
            'fax'             =>    'nullable|string|unique:vendors,fax',
            'tl_number'       =>    'required|string|unique:vendors,tl_number,' . $vendorId,
            'tl_expiry'       =>    'required|date',
            'owner_association_id' => 'required|integer|exists:owner_associations,id',
            'risk_policy_expiry' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'address_line_1.required' => 'Company address is required.',
        ];
    }
}
