<?php
namespace App\Http\Requests\OwnerAssociation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,jpeg,png',
            'phone' => 'required|string|unique:owner_associations',
            'email' => 'required|email|unique:owner_associations',
            'trn_number' => 'required|string|unique:owner_associations',
            'address' => 'required|string',
            'active' => 'required',
            'bank_account_number' => 'required|string',
            'trn_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'trade_license' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'chamber_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'memorandum_of_association' => 'nullable|file|mimes:pdf,jpg,jpeg,png'
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'The phone number must be required.',
            'phone.string' => 'The phone number must be a valid integer.',
            'phone.unique' => 'The phone number has already been taken.',
        ];
    }
}

