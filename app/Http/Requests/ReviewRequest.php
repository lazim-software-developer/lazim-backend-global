<?php

namespace App\Http\Requests;

use App\Enums\ReviewType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
     * Convert 'type' from label to enum value before validation
     */
    public function prepareForValidation()
    {
        if ($this->has('type')) {
            $this->merge([
                'type' => ReviewType::fromLabel($this->type)?->value ?? $this->type,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required | exists:users,id',
            'oa_id' => 'required | exists:owner_associations,id',
            'flat_id' => 'required | exists:flats,id',
            'type' => ['required', new Enum(ReviewType::class)],
            'comment' => 'nullable | string',
            'feedback' => ['required', 'integer', 'in:1,2,3'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'Selected user does not exist.',
            'oa_id.exists' => 'Selected owner association does not exist.',
            'flat_id.exists' => 'Selected flat does not exist.',
            'feedback.in' => 'Feedback must be 1 (good), 2 (average), or 3 (bad).',
        ];
    }
}
