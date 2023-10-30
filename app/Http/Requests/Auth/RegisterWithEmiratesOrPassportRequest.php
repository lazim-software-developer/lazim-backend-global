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
            'passport' => 'required_without:emirates_id|string',
            'emirates_id' => 'required_without:passport|string',
            'flat_id' => 'required|exists:flats,id',
            'building_id' => 'required|exists:buildings,id',
            'type' => 'required|in:Owner,Tenant',
        ];
    }
}
