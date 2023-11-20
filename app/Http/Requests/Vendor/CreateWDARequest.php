<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CreateWDARequest extends FormRequest
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
            'contract_id' => 'required|exists:contracts,id',
            'date' => 'required|date',
            'job_description' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
        ];
    }
}
