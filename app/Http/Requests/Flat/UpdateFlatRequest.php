<?php

namespace App\Http\Requests\Flat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateFlatRequest extends FormRequest
{
    public function rules()
    {
        $id = $this->segment(3);
        return [
            'floor' => [
                'required',
                'max:255',
                'string',
                Rule::unique('flats')->where(function ($query) {
                    return $query->where('building_id', $this->building_id)
                                ->where('owner_association_id', $this->owner_association_id);
                })->ignore($this->id)
            ],
            'building_id' => 'required|integer|exists:buildings,id',
            'owner_association_id' => 'required|integer|exists:owner_associations,id',
            'description' => 'required|string',
            'property_number' => 'required|string',
            'property_type' => 'required|string',
            'suit_area' => 'required|string',
            'actual_area' => 'required|string',
            'balcony_area' => 'required|string',
            'applicable_area' => 'required|string',
            'virtual_account_number' => 'required|string',
            'parking_count' => 'required|integer',
            'plot_number' => 'required|integer',
        ];
    }
    public function messages(): array
    {
        return [
            'floor.unique' => 'A Flat with this Floor already exists with the same Building and owner association.',
        ];
    }
}
