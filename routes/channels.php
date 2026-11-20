<?php

use App\Models\Arena;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Public channels (event.{id}, category.{id}) need no auth and carry only
| non-sensitive, already-public information. Private arena channels are
| limited to staff assigned to that arena.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private arena channel: only active staff assigned to that arena (or
// global roles with audit access) may subscribe.
Broadcast::channel('arena.{arenaId}', function (User $user, int $arenaId) {
    // Global oversight roles (audit/evidence) may watch any arena.
    if ($user->hasAnyPermission(['audit.view', 'evidence.view'])) {
        return true;
    }

    // Everyone else (judges, operators) must be actively assigned to it.
    return $user->arenaAssignments()
        ->where('arena_id', $arenaId)
        ->where('is_active', true)
        ->exists();
});
