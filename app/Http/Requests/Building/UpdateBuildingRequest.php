<?php

namespace App\Http\Requests\Building;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
{
    public function rules()
    {
        $id = $this->segment(3);
        return [
            'name' => 'required|string|max:255',
            'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'property_group_id' => 'required|string',
            'address_line1' => 'required|string',
            'area' => 'required|string',
            'city_id' => 'required|integer|exists:cities,id',
            'description' => 'required|string',
            'floors' => 'required|integer',
            'owner_association_id' => 'required|integer|exists:owner_associations,id',
            'slug' => 'unique:buildings',
        ];
    }
    public function messages(): array
    {
        return [
            'phone.required' => 'The phone number must be required.',
            'phone.string' => 'The phone number must be a valid integer.',
            'phone.unique' => 'The phone number has already been taken.',
        ];
    }
}
