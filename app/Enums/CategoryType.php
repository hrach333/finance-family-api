<?php

namespace App\Enums;

enum CategoryType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public static function fromFrontend(string $value): self
    {
        return match (strtoupper($value)) {
            'INCOME' => self::INCOME,
            'EXPENSE' => self::EXPENSE,
            default => throw new \InvalidArgumentException('Unsupported category type'),
        };
    }

    public function toFrontend(): string
    {
        return strtoupper($this->value);
    }
}
