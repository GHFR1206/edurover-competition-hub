<?php

namespace App\Domain\Scoring;

/**
 * Result of a scoring calculation.
 */
final readonly class ScoreResult
{
    public function __construct(
        public int $subtotal,
        public int $penaltyTotal,
        public int $total,
        /** @var array<int, array{score_object_id:int, computed:int, quantity:int, achieved:bool}> */
        public array $lines = [],
    ) {}
}
