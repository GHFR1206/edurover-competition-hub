<?php

namespace App\Enums;

/**
 * Education level. One category per level per event.
 */
enum Level: string
{
    case Sd = 'sd';
    case Smp = 'smp';
    case Sma = 'sma';

    public function label(): string
    {
        return match ($this) {
            self::Sd => 'SD',
            self::Smp => 'SMP',
            self::Sma => 'SMA',
        };
    }

    public function challengeName(): string
    {
        return match ($this) {
            self::Sd => 'Color Delivery Adventure',
            self::Smp => 'Smart Warehouse',
            self::Sma => 'Disaster Response',
        };
    }

    public function persona(): string
    {
        return match ($this) {
            self::Sd => 'Explorer',
            self::Smp => 'Programmer',
            self::Sma => 'Innovator',
        };
    }
}
