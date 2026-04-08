<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'groupId' => $this->group_id,
            'type' => $this->type?->toFrontend(),
            'name' => $this->name,
            'iconKey' => $this->icon_key,
        ];
    }
}
