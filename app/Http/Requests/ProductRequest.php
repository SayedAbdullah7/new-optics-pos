<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $productId = $this->route('product')?->id ?? null;
        
        $rules = [
            'item_code' => 'required|integer|unique:products,item_code,' . $productId,
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Add validation rules for each locale
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = 'required|string|max:255';
            $rules[$locale . '.description'] = 'nullable|string|max:1000';
        }

        return $rules;
    }


    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'purchase_price.required' => 'Purchase price is required.',
            'sale_price.required' => 'Sale price is required.',
        ];
    }
}





