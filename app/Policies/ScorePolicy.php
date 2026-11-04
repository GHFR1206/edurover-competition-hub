<?php

namespace App\Policies;

use App\Enums\Role as RoleEnum;
use App\Enums\ScoreStatus;
use App\Models\Run;
use App\Models\Score;
use App\Models\User;

/**
 * Enforces maker-checker: the judge who authors a score is never the one who approves it.
 */
class ScorePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['score.input', 'score.review', 'results.view.all', 'audit.view']);
    }

    public function view(User $user, Score $score): bool
    {
        // A judge only sees their own scores.
        if ($user->isRole(RoleEnum::Judge) && ! $user->hasAnyPermission(['score.review', 'results.view.all'])) {
            return $score->judge_id === $user->id;
        }

        return $user->hasAnyPermission(['score.review', 'results.view.all', 'audit.view', 'evidence.view']);
    }

    public function create(User $user, Run $run): bool
    {
        return $user->hasPermissionTo('score.input') && $this->assignedToRun($user, $run);
    }

    public function update(User $user, Score $score): bool
    {
        if (! $user->hasPermissionTo('score.input')) {
            return false;
        }

        // Frozen scores can only change through an official reopen.
        if ($score->status === ScoreStatus::Approved) {
            return false;
        }

        return $score->judge_id === $user->id;
    }

    public function submit(User $user, Score $score): bool
    {
        return $user->hasPermissionTo('score.submit') && $score->judge_id === $user->id;
    }

    public function approve(User $user, Score $score): bool
    {
        // Maker-checker: an approver must not be the author.
        return $user->hasPermissionTo('score.approve') && $score->judge_id !== $user->id;
    }

    public function reopen(User $user, Score $score): bool
    {
        return $user->hasPermissionTo('score.reopen') && $score->judge_id !== $user->id;
    }

    private function assignedToRun(User $user, Run $run): bool
    {
        if ($user->hasRole(RoleEnum::SuperAdmin->value)) {
            return false; // Super Admin never inputs scores.
        }

        return $user->arenaAssignments()
            ->where('arena_id', $run->arena_id)
            ->where('role', 'judge')
            ->where('is_active', true)
            ->exists();
    }
}
