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
            'category' => 'required_if:complaint_type,tenant_complaint,help_desk',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'complaint' => 'required|min:5|max:150',
            'complaint_type' => 'required|in:help_desk,tenant_complaint,suggestions,enquiries,snag',
            'complaint_details' => [
                'required_if:complaint_type,tenant_complaint,suggestions,enquiries',
                'string','max:1000','min:20'
            ],
            'flat_id' => 'required_if:complaint_type,tenant_complaint,suggestions,enquiries,help_desk'
        ];
    }

    public function messages()
    {
        return [
            'complaint_type.in' => 'Complaint type must be one of these options: help_desk, tenant_complaint, suggestions, enquiries',
            'complaint.min' => 'The Title field must be at least 5 characters.',
            'complaint.max' => 'The Title field must not exceed 150 characters.',
            'complaint_details.min' => 'The Details field must be at least 20 characters.',
            'complaint_details.max' => 'The Details field must not exceed 1000 characters.',
        ];
    }
}
