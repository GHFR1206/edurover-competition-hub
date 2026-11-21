<?php

namespace App\Domain\Scoring;

use InvalidArgumentException;

/**
 * Pure scoring calculator. No database, no framework dependencies.
 *
 * Total = Σ(points_per_unit × quantity) for normal objects − Σ(penalty points).
 * No maximum cap, no percentage weighting.
 */
final class ScoringEngine
{
    /**
     * @param  iterable<ScoreLineInput>  $lines
     */
    public function calculate(iterable $lines): ScoreResult
    {
        $subtotal = 0;
        $penaltyTotal = 0;
        $detail = [];

        foreach ($lines as $line) {
            $this->assertQuantityWithinLimit($line);

            $effectiveQuantity = $this->effectiveQuantity($line);
            $computed = $line->pointsPerUnit * $effectiveQuantity;

            if ($line->isPenalty) {
                // Penalty magnitude is always positive; it is subtracted from the subtotal.
                $penaltyTotal += abs($computed);
            } else {
                $subtotal += $computed;
            }

            $detail[] = [
                'score_object_id' => $line->scoreObjectId,
                'computed' => $computed,
                'quantity' => $effectiveQuantity,
                'achieved' => $line->achieved,
            ];
        }

        return new ScoreResult(
            subtotal: $subtotal,
            penaltyTotal: $penaltyTotal,
            total: $subtotal - $penaltyTotal,
            lines: $detail,
        );
    }

    private function effectiveQuantity(ScoreLineInput $line): int
    {
        // The UI offers both a "reached/occurred" checkbox and a quantity
        // field. A checked box with a blank quantity means "once" — this
        // applies to penalties too, otherwise ticking "pelanggaran terjadi"
        // while leaving the number at 0 would silently deduct nothing.
        if ($line->achieved && $line->quantity === 0) {
            return 1;
        }

        return max(0, $line->quantity);
    }

    private function assertQuantityWithinLimit(ScoreLineInput $line): void
    {
        if ($line->maxQuantity !== null && $line->quantity > $line->maxQuantity) {
            throw new InvalidArgumentException(
                "Quantity for score object {$line->code} exceeds configured maximum of {$line->maxQuantity}."
            );
        }
    }
}
