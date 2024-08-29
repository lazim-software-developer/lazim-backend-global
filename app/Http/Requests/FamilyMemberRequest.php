<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FamilyMemberRequest extends FormRequest
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
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'passport_number' => 'required',
            'passport_expiry_date' => 'required|date_format:Y-m-d',
            'emirates_id' => 'required',
            'emirates_expiry_date' => 'required|date_format:Y-m-d',
            'gender' => 'required|string|in:Male,Female,Others',
            'relation' => 'required|string',
            'flat_id' => 'required|exists:flats,id',
        ];
    }
}
