<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'groupId' => ['required', 'integer', 'exists:finance_groups,id'],
            'userId' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:CASH,CARD,BANK,SAVINGS'],
            'currency' => ['required', 'string', 'size:3'],
            'initialBalance' => ['required', 'numeric'],
            'shared' => ['required', 'boolean'],
        ];
    }
}
