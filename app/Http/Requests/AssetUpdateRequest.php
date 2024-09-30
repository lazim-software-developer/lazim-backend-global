<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetUpdateRequest extends FormRequest
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
            'name'                 => 'required|max:50',
            'location'             => 'required|max:100',
            'description'          => 'required|max:150',
            'floor'                => 'required|max:5',
            'division'             => 'required|max:50',
            'discipline'           => 'required|max:50',
            'frequency_of_service' => 'required|numeric|max:1000',
        ];
    }
}
