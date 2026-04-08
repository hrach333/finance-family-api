<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsService
{
    public function summary(int $groupId, ?string $startDate, ?string $endDate): array
    {
        $query = Transaction::query()->where('group_id', $groupId);
        $this->applyDateFilter($query, $startDate, $endDate);

        $income = (clone $query)
            ->where('type', TransactionType::INCOME)
            ->sum('amount');

        $expense = (clone $query)
            ->where('type', TransactionType::EXPENSE)
            ->sum('amount');

        $balance = Account::query()
            ->where('group_id', $groupId)
            ->where('is_active', true)
            ->sum('current_balance');

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'balance' => (float) $balance,
            'transactionsCount' => (clone $query)->count(),
        ];
    }

    protected function applyDateFilter(Builder $query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
    }
}
