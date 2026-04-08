<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'groupId' => $this->group_id,
            'userId' => $this->user_id,
            'name' => $this->name,
            'type' => $this->type?->toFrontend(),
            'currency' => $this->currency,
            'initialBalance' => (float) $this->initial_balance,
            'currentBalance' => (float) $this->current_balance,
            'shared' => (bool) $this->is_shared,
            'isActive' => (bool) $this->is_active,
        ];
    }
}
