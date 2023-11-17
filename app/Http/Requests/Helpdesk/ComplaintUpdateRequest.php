<?php

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintUpdateRequest extends FormRequest
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
            "priority" => "integer|min:1|max:3|required_without_all:due_date,technician_id",
            "due_date" => "date|required_without_all:priority,technician_id",
            "technician_id" => "integer|exists:users,id|required_without_all:priority,due_date",
        ]; 
    }

    public function messages()
{
    return [
        'priority.required_without_all' => 'At least one of the fields (Priority, Due Date, Technician ID) is required.',
        'due_date.required_without_all' => 'At least one of the fields (Priority, Due Date, Technician ID) is required.',
        'technician_id.required_without_all' => 'At least one of the fields (Priority, Due Date, Technician ID) is required.',
    ];
}
}
