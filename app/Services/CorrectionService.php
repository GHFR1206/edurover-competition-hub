<?php

namespace App\Services;

use App\Domain\Scoring\ScoreLineInput;
use App\Domain\Scoring\ScoringEngine;
use App\Events\StandingsUpdated;
use App\Models\Score;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Official score correction by the Chief Judge.
 *
 * Only an approved (already-official) score may be corrected. The chief can
 * override the quantity of any line; the subtotal, penalty and total are then
 * recomputed with the same pure engine used everywhere, and the change is
 * recorded as an append-only `corrected` decision with a mandatory reason.
 */
class CorrectionService
{
    public function __construct(private readonly ScoringEngine $engine = new ScoringEngine) {}

    /**
     * @param  array<int, array{line_id:int, quantity:int}>  $lines
     */
    public function correct(Score $score, User $chief, array $lines, string $reason): Score
    {
        if (! $score->status->isFrozen()) {
            throw new RuntimeException('Hanya skor resmi (disetujui) yang dapat dikoreksi.');
        }

        $score->load(['lines.scoreObject', 'run.category']);

        $byId = $score->lines->keyBy('id');
        $overrides = collect($lines)->keyBy('line_id');
        $changes = [];

        $inputs = [];

        foreach ($score->lines as $line) {
            $object = $line->scoreObject;

            if (! $object) {
                continue;
            }

            $quantity = $overrides->has($line->id)
                ? (int) $overrides->get($line->id)['quantity']
                : (int) $line->quantity;

            $inputs[] = new ScoreLineInput(
                scoreObjectId: $object->id,
                code: $object->code,
                pointsPerUnit: (int) $line->points_per_unit_snapshot,
                isPenalty: $object->type->isPenalty(),
                quantity: $quantity,
                achieved: (bool) $line->achieved,
                maxQuantity: $object->max_quantity,
            );

            if ($quantity !== (int) $line->quantity) {
                $changes[$line->id] = ['from' => (int) $line->quantity, 'to' => $quantity];
            }
        }

        if ($changes === []) {
            throw new RuntimeException('Tidak ada perubahan nilai yang dikirim.');
        }

        $result = $this->engine->calculate($inputs);
        $detail = collect($result->lines)->keyBy('score_object_id');

        $corrected = DB::transaction(function () use ($score, $chief, $changes, $detail, $reason, $result) {
            foreach ($score->lines as $line) {
                if (! isset($changes[$line->id])) {
                    continue;
                }

                $object = $line->scoreObject;
                $line->quantity = $changes[$line->id]['to'];
                $line->computed_points = (int) ($detail[$object->id]['computed'] ?? 0);
                $line->save();

                $line->writeAudit('updated', ['quantity' => $changes[$line->id]['from']], ['quantity' => $line->quantity], $reason);
            }

            $score->subtotal = $result->subtotal;
            $score->penalty_total = $result->penaltyTotal;
            $score->total = $result->total;
            $score->save();

            $score->approvals()->create([
                'run_id' => $score->run_id,
                'decision' => 'corrected',
                'reason' => $reason,
                'decided_by' => $chief->id,
                'decided_at' => now(),
            ]);

            $score->writeAudit('updated', null, [
                'corrected_lines' => $changes,
                'total' => $score->total,
            ], 'Koreksi resmi: '.$reason);

            return $score->fresh(['lines.scoreObject', 'approvals.decidedBy']);
        });

        // Official total changed → refresh public standings live.
        $score->loadMissing('run.category');
        StandingsUpdated::dispatch($score->run->category, $score->run->category->isPublished());

        return $corrected;
    }
}
