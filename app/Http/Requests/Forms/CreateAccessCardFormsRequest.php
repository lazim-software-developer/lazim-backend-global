<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccessCardFormsRequest extends FormRequest
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
            'mobile'=> ['required','regex:/^(?:\+971)?(?:50|51|52|55|56|2|3|4|6|7|9)\d{7}$/'],
            'email' => 'required|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
            'card_type'=>'required|string',
            'reason'=>'nullable|string',
            'parking_details'=>'nullable|json',
            'occupied_by' => 'required'
        ];
    }
}
