<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'paper_id' => 'nullable|exists:papers,id',
            'invoice_number' => 'nullable|string|max:50',
            'order_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:paid,partial,unpaid,canceled,cancelled',
            'invoiced_at' => 'required|date',
            'due_at' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'account_id' => 'nullable|exists:accounts,id',
            'paid' => 'nullable|numeric|min:0',

            // Products
            'product' => 'nullable|array',
            'product.*' => 'nullable|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|integer|min:1',
            'price' => 'nullable|array',
            'price.*' => 'nullable|numeric|min:0',

            // Lenses
            'lens_range' => 'nullable|array',
            'lens_range.*' => 'nullable|integer',
            'lens_type' => 'nullable|array',
            'lens_type.*' => 'nullable|integer',
            'lens_category' => 'nullable|array',
            'lens_category.*' => 'nullable|string',
            'lens_quantity' => 'nullable|array',
            'lens_quantity.*' => 'nullable|integer|min:2',
            'lens_price' => 'nullable|array',
            'lens_price.*' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client does not exist.',
            'invoiced_at.required' => 'Invoice date is required.',
        ];
    }
}
