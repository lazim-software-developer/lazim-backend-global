<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterWithEmiratesOrPassportRequest extends FormRequest
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
            'name' => 'required|string',
            'document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
            'emirates_document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
            'passport_document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
            'flat_id' => 'required|exists:flats,id',
            'building_id' => 'required|exists:buildings,id',
            'type' => 'required|in:Owner,Tenant',
            'owner_id' => 'nullable|integer',
            'email' => 'required|email',
            'mobile' => 'required|string',
        ];
    }
}
