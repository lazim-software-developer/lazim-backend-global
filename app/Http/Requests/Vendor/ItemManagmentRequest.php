<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class ItemManagmentRequest extends FormRequest
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
            'date' => 'required|date',
            'type' => 'required|in:incoming,used',
            'quantity' => 'required|integer|min:1',
            'comment' => 'required|string|max:150',
        ];
    }

    public function messages(){

        return [
            'quantity.integer' => 'The quantity field must be a number.',
        ];
    }
}
