<?php

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintStoreRequest extends FormRequest
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
            'category' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'complaint_type' => 'required|in:help_desk,tenant_complaint,suggestions,enquiries',
            'complaint_details' => [
                'required_if:complaint_type,tenant_complaint,suggestions,enquiries',
                'string',
            ],
        ];
    }

    public function messages()
    {
        return [
            'complaint_type.in' => 'Complaint type must be one of these options: help_desk, tenant_complaint, suggestions, enquiries',
        ];
    }
}
