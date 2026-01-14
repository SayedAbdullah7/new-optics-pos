<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:income,expense',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'category_id' => 'nullable|integer',
            'document_id' => 'nullable|integer',
            'contact_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Transaction type is required.',
            'account_id.required' => 'Please select an account.',
            'amount.required' => 'Transaction amount is required.',
            'amount.min' => 'Amount must be greater than 0.',
        ];
    }
}





