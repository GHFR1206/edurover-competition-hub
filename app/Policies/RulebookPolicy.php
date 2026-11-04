<?php

namespace App\Policies;

use App\Models\Rulebook;
use App\Models\User;

class RulebookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['rulebook.manage', 'event.manage', 'results.view.all']);
    }

    public function view(User $user, Rulebook $rulebook): bool
    {
        return $user->hasAnyPermission(['rulebook.manage', 'event.manage', 'results.view.all']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('rulebook.manage');
    }

    /** A locked rulebook is immutable; changes are rejected after lock. */
    public function update(User $user, Rulebook $rulebook): bool
    {
        return $user->hasPermissionTo('rulebook.manage') && ! $rulebook->isLocked();
    }

    public function lock(User $user, Rulebook $rulebook): bool
    {
        return $user->hasPermissionTo('rulebook.lock') && ! $rulebook->isLocked();
    }

    public function delete(User $user, Rulebook $rulebook): bool
    {
        return $user->hasPermissionTo('rulebook.manage') && ! $rulebook->isLocked();
    }
}
