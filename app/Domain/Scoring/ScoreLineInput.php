<?php

namespace App\Domain\Scoring;

/**
 * Immutable description of one scoreable line fed into the engine.
 */
final readonly class ScoreLineInput
{
    public function __construct(
        public int $scoreObjectId,
        public string $code,
        public int $pointsPerUnit,
        public bool $isPenalty,
        public int $quantity = 0,
        public bool $achieved = false,
        public ?int $maxQuantity = null,
    ) {}
}
