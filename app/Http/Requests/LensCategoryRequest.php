<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LensCategoryRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $category = $this->route('lens_category');
        $categoryId = $category ? (is_object($category) ? $category->id : $category) : null;

        return [
            'brand_name' => 'required|string|max:255|unique:lens_categories,brand_name,' . $categoryId,
            'country_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'brand_name.required' => 'Brand name is required.',
            'brand_name.unique' => 'This brand name already exists.',
            'brand_name.max' => 'Brand name cannot exceed 255 characters.',
            'country_name.max' => 'Country name cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'brand_name' => 'brand name',
            'country_name' => 'country name',
        ];
    }
}
