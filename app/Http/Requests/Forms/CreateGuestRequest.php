<?php

namespace App\Http\Requests\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateGuestRequest extends FormRequest
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
            'passport_number' => 'required|digits:10|not_in:000000000',
            'visa_validity_date' => 'required|date',
            'stay_duration' => 'required',
            'expiry_date' => 'required|date',
            'access_card_holder' => 'required',
            'original_passport' => 'required',
            'guest_registration' => 'required',
            'building_id' => 'required|integer',
            'flat_id' => 'required|integer',
            'name' => 'required|string',
            'phone' => ['required','regex:/^(?:\+971)?(?:50|51|52|55|56|2|3|4|6|7|9)\d{7}$/'],
            'start_date' => 'required|date',
            'number_of_visitors' => 'required|integer',
            'type' => 'required',
            'end_date' => 'required|date|after:start_date',
            'email' => 'required|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z]+\.[a-zA-Z]+$/',
        ];
    }
}
