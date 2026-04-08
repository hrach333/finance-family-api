<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'groupId' => $this->group_id,
            'accountId' => $this->account_id,
            'accountName' => $this->account?->name,
            'createdBy' => $this->created_by,
            'creatorName' => $this->creator?->name,
            'type' => $this->type?->toFrontend(),
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'categoryId' => $this->category_id,
            'categoryName' => $this->category?->name,
            'transferAccountId' => $this->transfer_account_id,
            'transactionDate' => optional($this->transaction_date)->format('Y-m-d'),
            'comment' => $this->comment,
            'createdAt' => optional($this->created_at)->toISOString(),
        ];
    }
}
