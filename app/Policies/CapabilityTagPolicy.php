<?php

namespace App\Policies;

use App\Models\CapabilityTag;
use App\Models\User;

class CapabilityTagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CapabilityTag $capabilityTag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }

    public function update(User $user, CapabilityTag $capabilityTag): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }

    public function delete(User $user, CapabilityTag $capabilityTag): bool
    {
        return $user->hasPermissionTo('challenge.manage');
    }
}
