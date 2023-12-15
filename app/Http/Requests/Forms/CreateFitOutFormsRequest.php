<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateFitOutFormsRequest extends FormRequest
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
            'building_id' => 'required|integer',
            'flat_id' => 'required|integer',
            'contractor_name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
            'no_objection'=>'required|integer',
            'undertaking_of_waterproofing'=>'required|integer',
        ];
    }
}
