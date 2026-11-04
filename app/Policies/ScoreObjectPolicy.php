<?php

namespace App\Policies;

use App\Models\ScoreObject;
use App\Models\User;

class ScoreObjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ScoreObject $scoreObject): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }

    public function update(User $user, ScoreObject $scoreObject): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }

    public function delete(User $user, ScoreObject $scoreObject): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }
}
