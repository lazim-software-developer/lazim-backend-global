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
        return [
            'owner_id'        =>    'required|integer|exists:users,id',
            'address_line_1'  =>    'required|string|unique:vendors,address_line_1',
            'address_line_2'  =>    'nullable|string',
            'landline_number' =>    'required|string|unique:vendors,landline_number',
            'website'         =>    'nullable|url',
            'fax'             =>    'nullable|string|unique:vendors,fax',
            'tl_number'       =>    'required|string|unique:vendors,tl_number',
            'tl_expiry'       =>    'required|date',
            'owner_association_id' => 'required|integer|exists:owner_associations,id',
            'risk_policy_expiry' => 'required|date',
        ];
    }
}
