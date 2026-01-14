<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LensTypeRequest extends FormRequest
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
        $typeId = $this->route('lens_type') ? $this->route('lens_type')->id : null;

        return [
            'name' => 'required|string|max:255|unique:lens_types,name,' . $typeId,
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Type name is required.',
            'name.unique' => 'This type name already exists.',
            'name.max' => 'Type name cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'type name',
        ];
    }
}
