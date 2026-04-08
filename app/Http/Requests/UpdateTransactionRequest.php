<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'accountId' => ['sometimes', 'integer', 'exists:accounts,id'],
            'createdBy' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['sometimes', 'string', 'in:INCOME,EXPENSE,TRANSFER'],
            'amount' => ['sometimes', 'numeric', 'gt:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'categoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'transferAccountId' => ['nullable', 'integer', 'exists:accounts,id'],
            'transactionDate' => ['sometimes', 'date'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
