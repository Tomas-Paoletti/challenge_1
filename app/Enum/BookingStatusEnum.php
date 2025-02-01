<?php

namespace App\Enum;

enum BookingStatusEnum: string
{
    case PENDING = '1';
    case CONFIRMED = '2';
    case CANCELLED = '3';

    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::CANCELLED,
        ];
    }


    public function toString(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function fromValue(string $value): self
    {
        return match ($value) {
            'Pending' => self::PENDING,
            'Confirmed' => self::CONFIRMED,
           'Cancelled' => self::CANCELLED,
        };
    }

}
