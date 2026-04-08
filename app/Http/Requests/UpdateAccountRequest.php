<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:CASH,CARD,BANK,SAVINGS'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'isActive' => ['sometimes', 'boolean'],
            'shared' => ['sometimes', 'boolean'],
        ];
    }
}
