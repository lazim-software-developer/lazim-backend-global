<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
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
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company_name' => 'required|string|max:150',
            'email' => 'required|email|max:50',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string',
            'number_of_communities' => 'required|integer|max:10000',
            'number_of_units' => 'required|integer|max:100000',
            'message' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'onboarding_assistance' => 'nullable|array',
            'onboarding_assistance.*' => 'string',
            'support' => 'nullable|array',
            'support.*' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Name is required',
            'first_name.string' => 'Name must be a string',
            'first_name.max' => 'Name cannot exceed 100 characters',
            'last_name.string' => 'Last name must be a string',
            'last_name.max' => 'Last name cannot exceed 100 characters',
            'company_name.required' => 'Company name is required',
            'company_name.string' => 'Company name must be a string',
            'company_name.max' => 'Company name cannot exceed 150 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.max' => 'Email cannot exceed 50 characters',
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone number must be a string',
            'phone.max' => 'Phone number cannot exceed 15 characters',
            'address.string' => 'Address must be a string',
            'address.max' => 'Address cannot exceed 255 characters',
            'state.string' => 'State must be a string',
            'number_of_communities.required' => 'Number of communities is required',
            'number_of_communities.integer' => 'Number of communities must be an integer',
            'number_of_communities.max' => 'Number of communities cannot exceed 10000',
            'number_of_units.required' => 'Number of units is required',
            'number_of_units.integer' => 'Number of units must be an integer',
            'number_of_units.max' => 'Number of units cannot exceed 100000',
            'message.string' => 'Message must be a string',
            'message.max' => 'Message cannot exceed 1000 characters',
            'features.*' => 'Features must be an array of strings',
            'onboarding_assistance.*' => 'Onboarding assistance must be an array of strings',
            'support.*' => 'Support must be an array of strings',
        ];
    }
}
