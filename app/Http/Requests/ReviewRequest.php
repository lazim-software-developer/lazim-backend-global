<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
            'user_id'   => 'required | exists:users,id',
            'oa_id'     => 'required | exists:owner_associations,id',
            'flat_id'   => 'required | exists:flats,id',
            'type'      => 'required|string|in:feedback',
            'comment'   => 'nullable | string',
            'feedback'  => 'required | integer', 'in:1,2,3',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists'  => 'Selected user does not exist.',
            'oa_id.exists'    => 'Selected owner association does not exist.',
            'flat_id.exists'  => 'Selected flat does not exist.',
            'feedback.in'     => 'Feedback must be 1 (good), 2 (average), or 3 (bad).',
        ];
    }
}
