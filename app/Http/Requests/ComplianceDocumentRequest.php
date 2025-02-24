<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplianceDocumentRequest extends FormRequest
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
            'doc_name'    => 'required|max:50',
            'expiry_date' => 'required|date',
            'url'         => 'sometimes|file|mimes:pdf|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doc_name.required'    => 'Document name is required',
            'doc_name.max'         => 'Document name cannot exceed 50 characters',
            'expiry_date.required' => 'Expiry date is required',
            'expiry_date.date'     => 'Please enter a valid date',
            'url.file'            => 'Please upload a valid file',
            'url.mimes'           => 'Only PDF files are allowed',
            'url.max'             => 'File size should not exceed 2MB'
        ];
    }
}
