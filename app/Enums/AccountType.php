<?php

namespace App\Enums;

enum AccountType: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case SAVINGS = 'savings';

    public static function fromFrontend(string $value): self
    {
        return match (strtoupper($value)) {
            'CASH' => self::CASH,
            'CARD' => self::CARD,
            'SAVINGS' => self::SAVINGS,
            default => throw new \InvalidArgumentException('Unsupported account type'),
        };
    }

    public function toFrontend(): string
    {
        return strtoupper($this->value);
    }
}
