<?php

namespace App\Services;

use App\Enums\ScoreStatus;
use App\Models\Category;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Support\Collection;

/**
 * Computes category standings from approved scores only.
 * One row per team; the team's best settled run contributes its total.
 */
class StandingsService
{
    /**
     * @return Collection<int, array{rank:int, team:Team, best_total:int, runs:int, approved:bool}>
     */
    public function forCategory(Category $category): Collection
    {
        $runs = Run::with(['team.school', 'scores'])
            ->where('category_id', $category->id)
            ->get();

        $byTeam = $runs->groupBy('team_id');

        $rows = $byTeam->map(function (Collection $teamRuns) {
            $team = $teamRuns->first()->team;

            $approvedTotals = $teamRuns
                ->flatMap(fn (Run $run) => $run->scores)
                ->filter(fn ($score) => $score->status === ScoreStatus::Approved)
                ->map(fn ($score) => (int) $score->total);

            $best = $approvedTotals->max() ?? 0;

            return [
                'team' => $team,
                'best_total' => $best,
                'runs' => $teamRuns->count(),
                'approved' => $approvedTotals->isNotEmpty(),
            ];
        })->values();

        return $rows
            ->sortByDesc('best_total')
            ->values()
            ->map(fn (array $row, int $i) => ['rank' => $i + 1] + $row);
    }

    /** Whether any approved score exists for the category (i.e. safe to show publicly). */
    public function hasApprovedScores(Category $category): bool
    {
        return Run::where('category_id', $category->id)
            ->whereHas('scores', fn ($q) => $q->where('status', ScoreStatus::Approved->value))
            ->exists();
    }
}
