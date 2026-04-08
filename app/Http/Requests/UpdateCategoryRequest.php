<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:INCOME,EXPENSE'],
            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
