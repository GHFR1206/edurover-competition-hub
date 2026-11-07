<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

/**
 * Team registration verification is restricted to Event Admin and Super Admin
 * (permissions: registration.manage + registration.verify).
 */
class RegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['registration.manage', 'registration.verify']);
    }

    public function view(User $user, Registration $registration): bool
    {
        return $user->hasAnyPermission(['registration.manage', 'registration.verify']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('registration.manage');
    }

    public function update(User $user, Registration $registration): bool
    {
        return $user->hasPermissionTo('registration.manage');
    }

    public function verify(User $user, Registration $registration): bool
    {
        return $user->hasPermissionTo('registration.verify');
    }

    public function reject(User $user, Registration $registration): bool
    {
        return $user->hasPermissionTo('registration.verify');
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $user->hasPermissionTo('registration.manage');
    }
}
