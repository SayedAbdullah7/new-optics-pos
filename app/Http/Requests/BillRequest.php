<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $billId = $this->route('bill')?->id ?? null;
        
        return [
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:50|unique:bills,bill_number,' . $billId,
            'order_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:paid,partial,unpaid',
            'billed_at' => 'required|date',
            'due_at' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'account_id' => 'required|exists:accounts,id',
            'paid' => 'nullable|numeric|min:0',

            // Products
            'product' => 'nullable|array',
            'product.*' => 'nullable|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|integer|min:1',
            'price' => 'nullable|array',
            'price.*' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_id.required' => 'Please select a vendor.',
            'vendor_id.exists' => 'The selected vendor does not exist.',
            'billed_at.required' => 'Bill date is required.',
            'amount.required' => 'Bill amount is required.',
        ];
    }
}





