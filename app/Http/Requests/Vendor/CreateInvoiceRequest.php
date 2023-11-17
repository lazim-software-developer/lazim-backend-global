<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
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
            'date' => 'required|date',
            'document' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
            'wda_id' => 'required|exists:wda,id',
            'invoice_amount' => 'required|integer',
            'remarks' => 'nullable|string|max:255',
        ];
    }
}
