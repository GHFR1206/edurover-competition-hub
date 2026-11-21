<?php

namespace App\Services;

use App\Enums\RunStatus;
use App\Enums\ScoreStatus;
use App\Models\Category;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Support\Collection;

/**
 * Computes category standings.
 *
 * Two scoring modes are supported per category (`categories.scoring_mode`):
 *
 * - `jury` (default): the official value is the judges' approved score. A run
 *   is first aggregated across judges using the rulebook's
 *   `aggregation.judges` (average|best|total), then a team's attempts are
 *   aggregated using `aggregation.attempt` (best|total|average).
 * - `time`: the official value is the fastest arena timing (`official_time_ms`)
 *   of the team's settled runs; lower is better.
 *
 * Ties are broken with the rulebook's `tie_break_order` (default
 * achievement -> penalty -> time -> extra run).
 */
class StandingsService
{
    private const DEFAULT_AGGREGATION = ['attempt' => 'best', 'judges' => 'average'];

    private const DEFAULT_TIE_BREAK = ['primary_achievement', 'penalty', 'time', 'extra_run'];

    /** Human-readable stopwatch value, e.g. `01:23.456`. */
    public static function formatMs(?int $ms): string
    {
        if ($ms === null) {
            return '—';
        }

        $minutes = intdiv($ms, 60_000);
        $seconds = intdiv($ms % 60_000, 1000);
        $millis = $ms % 1000;

        return sprintf('%02d:%02d.%03d', $minutes, $seconds, $millis);
    }

    /**
     * @return Collection<int, array{
     *     rank:int, team:Team, best_total:int, runs:int, approved:bool,
     *     time_ms:?int, lap_count:int, mode:string, tie_break:string
     * }>
     */
    public function forCategory(Category $category): Collection
    {
        $runs = Run::with(['team.school', 'scores.lines.scoreObject', 'laps'])
            ->where('category_id', $category->id)
            ->get();

        $rulebook = $category->activeRulebook ?? $category->rulebooks()->latest('version')->first();

        $aggregation = array_merge(self::DEFAULT_AGGREGATION, (array) ($rulebook?->aggregation ?? []));
        $tieBreak = (array) ($rulebook?->tie_break_order ?: self::DEFAULT_TIE_BREAK);
        $mode = $category->scoring_mode ?: 'jury';

        $rows = $runs->groupBy('team_id')
            ->map(fn (Collection $teamRuns) => $this->teamRow(
                $teamRuns,
                $mode,
                $aggregation,
                $tieBreak,
            ))
            ->values();

        // Teams without any run are not ranked (they have nothing to compare).
        return $rows
            ->sort(fn (array $a, array $b) => $this->compare($a, $b, $mode))
            ->values()
            ->map(fn (array $row, int $i) => ['rank' => $i + 1] + $row);
    }

    /** Whether any official value exists for the category (safe to show publicly). */
    public function hasApprovedScores(Category $category): bool
    {
        if (($category->scoring_mode ?: 'jury') === 'time') {
            // Only a finished run has a valid official time; a DQ/DNF/no-show
            // may carry a raw stopwatch reading but must never be rankable.
            return Run::where('category_id', $category->id)
                ->whereNotNull('official_time_ms')
                ->where('status', RunStatus::Finished->value)
                ->exists();
        }

        return Run::where('category_id', $category->id)
            ->whereHas('scores', fn ($q) => $q->where('status', ScoreStatus::Approved->value))
            ->exists();
    }

    /**
     * @param  Collection<int, Run>  $teamRuns
     * @param  array<string, string>  $aggregation
     * @param  array<int, string>  $tieBreak
     * @return array<string, mixed>
     */
    private function teamRow(Collection $teamRuns, string $mode, array $aggregation, array $tieBreak): array
    {
        $team = $teamRuns->first()->team;

        $perRun = $teamRuns->map(fn (Run $run) => $this->runValue($run, $mode, $aggregation));

        $hasOfficial = $perRun->contains(fn (array $r) => $r['official']);

        // Attempt aggregation: a team's result over several runs.
        $values = $perRun->filter(fn (array $r) => $r['official'])->pluck('value');

        $bestTotal = match ($aggregation['attempt'] ?? 'best') {
            'total' => (int) $values->sum(),
            'average' => $values->isEmpty() ? 0 : (int) round($values->avg()),
            default => (int) ($values->max() ?? 0),
        };

        // Official time only counts for a run that actually finished: a DQ or
        // a no-show must never win a time-based category just because the
        // device happened to stop the clock.
        $times = $teamRuns
            ->filter(fn (Run $run) => $run->status === RunStatus::Finished && $run->official_time_ms !== null)
            ->pluck('official_time_ms');

        $timeMs = $times->isEmpty() ? null : (int) $times->min();

        $lapCount = (int) $teamRuns->sum(fn (Run $run) => $run->lap_count ?? $run->laps->count());

        // Tie-break ingredients, computed once per team.
        $achievements = $teamRuns->flatMap(fn (Run $run) => $run->scores)
            ->filter(fn ($score) => $score->status === ScoreStatus::Approved)
            ->flatMap(fn ($score) => $score->lines)
            ->filter(fn ($line) => $line->achieved && $line->scoreObject?->type->value === 'achievement')
            ->count();

        $penalties = $teamRuns->flatMap(fn (Run $run) => $run->scores)
            ->filter(fn ($score) => $score->status === ScoreStatus::Approved)
            ->sum(fn ($score) => (int) $score->penalty_total);

        return [
            'team' => $team,
            'best_total' => $mode === 'time' ? 0 : $bestTotal,
            'runs' => $teamRuns->count(),
            'approved' => $hasOfficial,
            'time_ms' => $timeMs,
            'lap_count' => $lapCount,
            'mode' => $mode,
            'tie_break' => implode('>', $tieBreak),
            '_achievements' => $achievements,
            '_penalties' => $penalties,
            '_extra_runs' => $teamRuns->count(),
        ];
    }

    /**
     * Value of a single run.
     *
     * @param  array<string, string>  $aggregation
     * @return array{official:bool, value:int}
     */
    private function runValue(Run $run, string $mode, array $aggregation): array
    {
        if ($mode === 'time') {
            $official = $run->status === RunStatus::Finished && $run->official_time_ms !== null;

            return [
                'official' => $official,
                // Lower time wins; store the negative so "higher is better"
                // sorting stays uniform inside compare().
                'value' => $official ? -1 * (int) $run->official_time_ms : 0,
            ];
        }

        $approved = $run->scores
            ->filter(fn ($score) => $score->status === ScoreStatus::Approved)
            ->map(fn ($score) => (int) $score->total);

        if ($approved->isEmpty()) {
            return ['official' => false, 'value' => 0];
        }

        $value = match ($aggregation['judges'] ?? 'average') {
            'best' => (int) $approved->max(),
            'total' => (int) $approved->sum(),
            default => (int) round($approved->avg()),
        };

        return ['official' => true, 'value' => $value];
    }

    /**
     * Sort comparator: primary metric first, then the rulebook tie-break order.
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function compare(array $a, array $b, string $mode): int
    {
        // Teams with no official result always rank below teams that have one.
        if ($a['approved'] !== $b['approved']) {
            return $a['approved'] ? -1 : 1;
        }

        if ($mode === 'time') {
            $cmp = $this->ascending($a['time_ms'], $b['time_ms']);

            if ($cmp !== 0) {
                return $cmp;
            }
        } else {
            $cmp = $b['best_total'] <=> $a['best_total'];

            if ($cmp !== 0) {
                return $cmp;
            }
        }

        foreach (explode('>', $a['tie_break']) as $rule) {
            $cmp = match ($rule) {
                'primary_achievement' => $b['_achievements'] <=> $a['_achievements'],
                'penalty' => $a['_penalties'] <=> $b['_penalties'],
                'time' => $this->ascending($a['time_ms'], $b['time_ms']),
                'extra_run' => $a['_extra_runs'] <=> $b['_extra_runs'],
                default => 0,
            };

            if ($cmp !== 0) {
                return $cmp;
            }
        }

        // Deterministic fallback so the order never flickers between requests.
        return strcmp($a['team']->name, $b['team']->name);
    }

    /** Nulls sort last (a team without a time loses the time tie-break). */
    private function ascending(?int $a, ?int $b): int
    {
        if ($a === $b) {
            return 0;
        }

        if ($a === null) {
            return 1;
        }

        if ($b === null) {
            return -1;
        }

        return $a <=> $b;
    }
}
