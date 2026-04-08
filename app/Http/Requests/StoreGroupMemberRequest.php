<?php

namespace App\Http\Requests;

use App\Enums\GroupRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['nullable', 'string', Rule::in([
                GroupRole::ADMIN->value,
                GroupRole::MEMBER->value,
            ])],
        ];
    }
}
