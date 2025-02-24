<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class DocumentsUploadRequest extends FormRequest
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
            'docs' => 'required|array',
            'docs.tl_document' => 'required|file|max:2048',
            'docs.trn_certificate' => 'required|file|max:2048',
            'docs.safety_policy'=> 'required|file|max:2048',
            'docs.bank_details' => 'required|file|max:2048',
            'docs.risk_policy' => 'required|file|max:2048',
            'docs.authority_approval' => 'nullable|file|max:2048',
            'docs.risk_assessment' => 'nullable|file|max:2048',
        ];
    }
}
