<?php

namespace App\Policies;

use App\Enums\Role as RoleEnum;
use App\Models\Run;
use App\Models\User;

class RunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['run.control', 'run.assign', 'score.input', 'results.view.all']);
    }

    public function view(User $user, Run $run): bool
    {
        if ($user->hasAnyPermission(['run.assign', 'results.view.all', 'audit.view'])) {
            return true;
        }

        // Judges see runs on arenas they are assigned to.
        if ($user->hasPermissionTo('score.input')) {
            return $this->assignedToArena($user, $run, 'judge');
        }

        // Operators control only their own arena.
        if ($user->hasPermissionTo('run.control')) {
            return $this->assignedToArena($user, $run, 'operator');
        }

        // Coaches see only their own team's runs.
        return $user->isRole(RoleEnum::Coach) && $this->ownsTeam($user, $run);
    }

    public function update(User $user, Run $run): bool
    {
        return $user->hasPermissionTo('run.control')
            && $this->assignedToArena($user, $run, 'operator');
    }

    private function assignedToArena(User $user, Run $run, string $role): bool
    {
        return $user->arenaAssignments()
            ->where('arena_id', $run->arena_id)
            ->where('role', $role)
            ->where('is_active', true)
            ->exists();
    }

    private function ownsTeam(User $user, Run $run): bool
    {
        return $user->coachProfile()
            ->whereHas('school.teams', fn ($q) => $q->whereKey($run->team_id))
            ->exists();
    }
}
