<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LensRequest extends FormRequest
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
        $lensId = $this->route('lens') ? $this->route('lens')->id : null;

        return [
            'lens_code' => 'required|string|max:100|unique:lenses,lens_code,' . $lensId,
            'RangePower_id' => 'required|exists:range_power,id',
            'type_id' => 'required|exists:lens_types,id',
            'category_id' => 'required|exists:lens_categories,id',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'lens_code.required' => 'Lens code is required.',
            'lens_code.unique' => 'This lens code already exists.',
            'RangePower_id.required' => 'Please select a range power.',
            'RangePower_id.exists' => 'The selected range power does not exist.',
            'type_id.required' => 'Please select a lens type.',
            'type_id.exists' => 'The selected lens type does not exist.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'sale_price.required' => 'Sale price is required.',
            'sale_price.min' => 'Sale price must be greater than or equal to 0.',
            'purchase_price.min' => 'Purchase price must be greater than or equal to 0.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'lens_code' => 'lens code',
            'RangePower_id' => 'range power',
            'type_id' => 'lens type',
            'category_id' => 'category',
            'sale_price' => 'sale price',
            'purchase_price' => 'purchase price',
        ];
    }
}
