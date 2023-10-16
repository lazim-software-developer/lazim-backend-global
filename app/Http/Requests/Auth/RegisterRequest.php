<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|unique:users,phone',
            'building_id' => 'required|integer',
            'flat_id' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'building_id' => "Please select a building",
            'flat_id' => "Please select a flat",
            'email.unique' => 'The provided email is already registered.',
            'mobile.unique' => 'The provided mobile number is already registered.',
        ];
    }
}
