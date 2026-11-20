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

    /**
     * Public-facing label used by the RoboRace front-end design
     * (SD / SLTP / SLTA instead of SD / SMP / SMA).
     */
    public function publicLabel(): string
    {
        return match ($this) {
            self::Sd => 'SD',
            self::Smp => 'SLTP',
            self::Sma => 'SLTA',
        };
    }

    /** Resolve a level from any of its aliases (lowercase, case-insensitive). */
    public static function fromAlias(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower($value);

        return match ($value) {
            'sd' => self::Sd,
            'smp', 'sltp' => self::Smp,
            'sma', 'slta', 'smk' => self::Sma,
            default => null,
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
