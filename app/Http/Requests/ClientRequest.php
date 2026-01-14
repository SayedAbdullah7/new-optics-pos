<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
        return [
            // Client fields
            'name' => 'required|string|max:255',
            'phone' => 'required|array|min:1',
            'phone.0' => 'required|string|max:50',
            'phone.*' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            
            // Paper (Prescription) fields
            'R_sph' => 'nullable|numeric|between:-30,30',
            'R_cyl' => 'nullable|numeric|between:-15,15',
            'R_axis' => 'nullable|integer|between:0,180',
            'L_sph' => 'nullable|numeric|between:-30,30',
            'L_cyl' => 'nullable|numeric|between:-15,15',
            'L_axis' => 'nullable|integer|between:0,180',
            'addtion' => 'nullable|numeric|between:0,5',
            'ipd' => 'nullable|numeric|between:40,80',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Client name is required.',
            'name.max' => 'Client name cannot exceed 255 characters.',
            'phone.required' => 'At least one phone number is required.',
            'phone.0.required' => 'Primary phone number is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'R_axis.between' => 'Right eye axis must be between 0 and 180.',
            'L_axis.between' => 'Left eye axis must be between 0 and 180.',
            'ipd.between' => 'IPD must be between 40 and 80.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'client name',
            'phone' => 'phone number',
            'phone.0' => 'primary phone number',
            'address' => 'address',
            'R_sph' => 'Right SPH',
            'R_cyl' => 'Right CYL',
            'R_axis' => 'Right Axis',
            'L_sph' => 'Left SPH',
            'L_cyl' => 'Left CYL',
            'L_axis' => 'Left Axis',
            'addtion' => 'Addition',
            'ipd' => 'IPD',
        ];
    }

    /**
     * Get only client data from the request.
     */
    public function clientData(): array
    {
        return $this->only(['name', 'phone', 'address']);
    }

    /**
     * Get only paper (prescription) data from the request.
     */
    public function paperData(): array
    {
        return $this->only(['R_sph', 'R_cyl', 'R_axis', 'L_sph', 'L_cyl', 'L_axis', 'addtion', 'ipd']);
    }

    /**
     * Check if paper data has any values.
     */
    public function hasPaperData(): bool
    {
        $paperData = $this->paperData();
        return collect($paperData)->filter(fn($value) => $value !== null && $value !== '')->isNotEmpty();
    }
}
