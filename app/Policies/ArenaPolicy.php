<?php

namespace App\Policies;

use App\Models\Arena;
use App\Models\User;

class ArenaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['arena.manage', 'run.control', 'results.view.arena', 'results.view.all']);
    }

    public function view(User $user, Arena $arena): bool
    {
        if ($user->hasAnyPermission(['arena.manage', 'results.view.all'])) {
            return true;
        }

        return $user->arenaAssignments()
            ->where('arena_id', $arena->id)
            ->where('is_active', true)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('arena.manage');
    }

    public function update(User $user, Arena $arena): bool
    {
        if ($user->hasPermissionTo('arena.manage')) {
            return true;
        }

        // Operators may only toggle status on arenas they are assigned to.
        return $user->hasPermissionTo('run.control')
            && $user->arenaAssignments()
                ->where('arena_id', $arena->id)
                ->where('role', 'operator')
                ->where('is_active', true)
                ->exists();
    }

    public function delete(User $user, Arena $arena): bool
    {
        return $user->hasPermissionTo('arena.manage');
    }
}
