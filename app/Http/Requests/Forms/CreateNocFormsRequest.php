<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateNocFormRequest extends FormRequest
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
            'name' => 'required|string',
            'type' =>'required',
            'mobile'=> 'required',
            'moving_date'=> 'required',
            'moving_time'=> 'required',
            'document_library_id'=> 'required',
            'preference'=> 'required',
            'email' => 'required|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
            // 'file' => 'required|file |max:2048'
        ];
    }
    public function messages()
    {
        return [
            'building_id' => "Please select a building",
            'flat_id' => "Please select a flat",
            'email.required' => 'Email is required.',
            'name.required' =>'Name is required.',
            'phone.required' =>'Phone is required.',
            'type.required' =>'Type is required.'
        ];
    }
}
