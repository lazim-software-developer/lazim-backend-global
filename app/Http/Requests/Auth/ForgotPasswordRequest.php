<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'owner_id' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'email.exists' => 'The provided email address is not registered in our system.',
        ];
    }
}

