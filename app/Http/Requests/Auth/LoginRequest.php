<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Everyone can make this request
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:Owner,Tenant'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be either Owner or Tenant.'
        ];
    }
}
