<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class PPMStoreRequest extends FormRequest
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
            'asset_id' => 'required|integer|exists:assets,id',
            'building_id' => 'required|integer|exists:buildings,id',
            'quarter' => 'required|integer|min:1|max:4',
            'file' => 'required|file|mimes:pdf,jpeg,png,doc,docx|max:2048',
        ];
    }
}
