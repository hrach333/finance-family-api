<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'groupId' => ['required', 'integer', 'exists:finance_groups,id'],
            'type' => ['required', 'string', 'in:INCOME,EXPENSE'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
