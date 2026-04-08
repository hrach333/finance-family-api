<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionService
{
    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            $transaction->load(['account', 'transferAccount']);
            $this->applyEffect($transaction);

            return $transaction->fresh(['account', 'category', 'creator', 'transferAccount']);
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->loadMissing(['account', 'transferAccount']);
            $this->revertEffect($transaction);
            $transaction->update($data);
            $transaction->refresh();
            $transaction->load(['account', 'transferAccount']);
            $this->applyEffect($transaction);

            return $transaction->fresh(['account', 'category', 'creator', 'transferAccount']);
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->loadMissing(['account', 'transferAccount']);
            $this->revertEffect($transaction);
            $transaction->delete();
        });
    }

    protected function applyEffect(Transaction $transaction): void
    {
        $amount = (float) $transaction->amount;
        $account = $transaction->account;

        if (!$account) throw new RuntimeException('Счет не найден.');

        if ($transaction->type === TransactionType::INCOME) {
            $account->increment('current_balance', $amount);
            return;
        }

        if ($transaction->type === TransactionType::EXPENSE) {
            $account->decrement('current_balance', $amount);
            return;
        }

        $target = $transaction->transferAccount;
        if (!$target) throw new RuntimeException('Для перевода не найден целевой счет.');
        $account->decrement('current_balance', $amount);
        $target->increment('current_balance', $amount);
    }

    protected function revertEffect(Transaction $transaction): void
    {
        $amount = (float) $transaction->amount;
        $account = $transaction->account;

        if (!$account) throw new RuntimeException('Счет не найден.');

        if ($transaction->type === TransactionType::INCOME) {
            $account->decrement('current_balance', $amount);
            return;
        }

        if ($transaction->type === TransactionType::EXPENSE) {
            $account->increment('current_balance', $amount);
            return;
        }

        $target = $transaction->transferAccount;
        if (!$target) throw new RuntimeException('Для перевода не найден целевой счет.');
        $account->increment('current_balance', $amount);
        $target->decrement('current_balance', $amount);
    }
}
