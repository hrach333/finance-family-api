<?php

namespace App\Http\Requests;

use App\Enums\GroupRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in([
                GroupRole::ADMIN->value,
                GroupRole::MEMBER->value,
            ])],
        ];
    }
}
