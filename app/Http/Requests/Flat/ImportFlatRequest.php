<?php

namespace App\Http\Requests\Flat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ImportFlatRequest extends FormRequest
{
    public function rules()
    {
        return [
            'file' => 'required|file|mimes:csv',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Please upload a CSV file',
            'file.mimes' => 'Only CSV files are allowed',
        ];
    }
}
