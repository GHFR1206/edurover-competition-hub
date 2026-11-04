<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;

class ChallengePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Challenge $challenge): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }
}
