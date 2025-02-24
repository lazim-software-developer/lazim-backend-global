<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidentRequest extends FormRequest
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
            // 'category' => 'required',
            'media' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mkv|max:2048',
            'complaint' => 'required|min:5|max:150',
            'complaint_type' => 'required|in:incident',
        ];
    }

    public function messages()
    {
        return [
            'complaint_type.in' => 'Complaint type must be incident',
            'complaint.min' => 'The Title field must be at least 5 characters.',
            'complaint.max' => 'The Title field must not exceed 150 characters.',
        ];
    }
}
