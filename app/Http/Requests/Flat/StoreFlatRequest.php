<?php
namespace App\Http\Requests\Flat;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlatRequest extends FormRequest
{
    public function rules()
    {
        return [
            'floor' => 'required|string|max:255',
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
}

