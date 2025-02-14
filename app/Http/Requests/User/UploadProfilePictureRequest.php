<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UploadProfilePictureRequest extends FormRequest
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
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Please select an image to upload.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'The image must be of type: jpeg, png, jpg.',
            'profile_picture.max' => 'The image must not be greater than 2MB.',
        ];
    }
}
