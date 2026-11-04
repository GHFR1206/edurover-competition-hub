<?php

namespace App\Services;

use App\Domain\Scoring\ScoreLineInput;
use App\Domain\Scoring\ScoringEngine;
use App\Enums\ScoreStatus;
use App\Models\Run;
use App\Models\Score;
use App\Models\ScoreLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Persists judge scoring into Score + ScoreLine rows using the pure ScoringEngine.
 */
class ScoringService
{
    public function __construct(private readonly ScoringEngine $engine = new ScoringEngine) {}

    /**
     * Create or replace the draft score for a run authored by the given judge.
     *
     * @param  array<int, array{score_object_id:int, quantity?:int, achieved?:bool, notes?:?string}>  $entries
     */
    public function saveDraft(Run $run, User $judge, array $entries): Score
    {
        return DB::transaction(function () use ($run, $judge, $entries) {
            $score = Score::firstOrNew([
                'run_id' => $run->id,
                'judge_id' => $judge->id,
            ]);

            $score->status = ScoreStatus::Draft;
            $score->version = ($score->version ?? 0) + 1;
            $score->save();

            $score->lines()->delete();

            $challenge = $run->category->challenges()->with('scoreObjects')->first();
            $objects = $challenge?->scoreObjects->keyBy('id') ?? collect();

            $inputs = [];
            $persisted = [];

            foreach ($entries as $entry) {
                $object = $objects->get((int) $entry['score_object_id']);

                if (! $object) {
                    continue;
                }

                $quantity = (int) ($entry['quantity'] ?? 0);
                $achieved = (bool) ($entry['achieved'] ?? false);

                $inputs[] = new ScoreLineInput(
                    scoreObjectId: $object->id,
                    code: $object->code,
                    pointsPerUnit: (int) $object->points_per_unit,
                    isPenalty: $object->type->isPenalty(),
                    quantity: $quantity,
                    achieved: $achieved,
                    maxQuantity: $object->max_quantity,
                );

                $persisted[] = [
                    'object' => $object,
                    'quantity' => $quantity,
                    'achieved' => $achieved,
                    'notes' => $entry['notes'] ?? null,
                ];
            }

            $result = $this->engine->calculate($inputs);
            $detail = collect($result->lines)->keyBy('score_object_id');

            foreach ($persisted as $row) {
                ScoreLine::create([
                    'score_id' => $score->id,
                    'score_object_id' => $row['object']->id,
                    'quantity' => $row['quantity'],
                    'achieved' => $row['achieved'],
                    'points_per_unit_snapshot' => (int) $row['object']->points_per_unit,
                    'computed_points' => (int) ($detail[$row['object']->id]['computed'] ?? 0),
                    'notes' => $row['notes'],
                ]);
            }

            $score->subtotal = $result->subtotal;
            $score->penalty_total = $result->penaltyTotal;
            $score->total = $result->total;
            $score->ruleset_snapshot = [
                'calculated_at' => now()->toIso8601String(),
                'lines' => $result->lines,
            ];
            $score->save();

            $score->writeAudit('updated', null, ['total' => $score->total], 'Penyimpanan draft skor');

            return $score->fresh(['lines.scoreObject']);
        });
    }

    /** Move a draft to Submitted (visible to the owning coach, but not yet official). */
    public function submit(Score $score, User $judge): Score
    {
        $score->status = ScoreStatus::Submitted;
        $score->submitted_at = now();
        $score->save();

        $score->writeAudit('updated', null, ['status' => 'submitted'], 'Pengajuan skor ke Chief Judge');

        return $score;
    }
}
