<?php

namespace App\Enums;

/**
 * Kinds of scoreable things configured per challenge.
 */
enum ScoreObjectType: string
{
    case Object = 'object';
    case Checkpoint = 'checkpoint';
    case Achievement = 'achievement';
    case Penalty = 'penalty';

    public function label(): string
    {
        return match ($this) {
            self::Object => 'Objek',
            self::Checkpoint => 'Checkpoint',
            self::Achievement => 'Pencapaian',
            self::Penalty => 'Penalti',
        };
    }

    /** Penalties subtract from the total; everything else adds. */
    public function isPenalty(): bool
    {
        return $this === self::Penalty;
    }
}
