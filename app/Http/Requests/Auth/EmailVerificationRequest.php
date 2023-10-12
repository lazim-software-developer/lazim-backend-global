<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change this if you have specific authorization logic
    }

    public function rules()
    {
        return [
            'otp' => 'required|string|max:6',
            'contact_value' => 'required',
            'type' => 'required'
        ];
    }
}

