<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'groupId' => ['required', 'integer', 'exists:finance_groups,id'],
            'accountId' => ['required', 'integer', 'exists:accounts,id'],
            'createdBy' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'in:INCOME,EXPENSE,TRANSFER'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
            'categoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'transferAccountId' => ['nullable', 'integer', 'exists:accounts,id'],
            'transactionDate' => ['required', 'date'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = strtoupper((string) $this->input('type'));
            if ($type === 'TRANSFER' && !$this->filled('transferAccountId')) {
                $validator->errors()->add('transferAccountId', 'Для перевода нужен второй счет.');
            }
            if (in_array($type, ['INCOME', 'EXPENSE'], true) && !$this->filled('categoryId')) {
                $validator->errors()->add('categoryId', 'Для дохода и расхода нужна категория.');
            }
        });
    }
}
