<?php

namespace App\Enums;

enum TransactionType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case TRANSFER = 'transfer';

    public static function fromFrontend(string $value): self
    {
        return match (strtoupper($value)) {
            'INCOME' => self::INCOME,
            'EXPENSE' => self::EXPENSE,
            'TRANSFER' => self::TRANSFER,
            default => throw new \InvalidArgumentException('Unsupported transaction type'),
        };
    }

    public function toFrontend(): string
    {
        return strtoupper($this->value);
    }
}
