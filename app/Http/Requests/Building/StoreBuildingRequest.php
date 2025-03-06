<?php
namespace App\Http\Requests\Building;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreBuildingRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('buildings')->where(function ($query) {
                    return $query->where('property_group_id', $this->property_group_id)
                                ->where('area', $this->area)
                                ->where('city_id', $this->city_id)
                                ->where('owner_association_id', $this->owner_association_id);
                })
            ],
            'cover_photo' => 'required|image|mimes:jpg,jpeg,png',
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
            'name.unique' => 'A building with this name already exists with the same property group, area, city and owner association.',
        ];
    }
}

