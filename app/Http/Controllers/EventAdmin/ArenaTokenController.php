<?php

namespace App\Http\Controllers\EventAdmin;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureArenaDevice;
use App\Models\Arena;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Arena timing tokens.
 *
 * Each arena gets a dedicated service account plus one Sanctum token with the
 * `arena:timing` ability. The plain token is shown exactly once (on creation);
 * afterwards only its prefix, creation time and last use are visible.
 * Revoking deletes the token immediately.
 */
class ArenaTokenController extends Controller
{
    public function index(Request $request): View
    {
        $event = $this->event();

        $arenas = Arena::with('apiUser')
            ->when($event, fn ($q) => $q->where('event_id', $event->id))
            ->orderBy('name')
            ->get();

        return view('event-admin.tokens', [
            'event' => $event,
            'arenas' => $arenas,
            'plainToken' => session('plain_token'),
            'plainArena' => session('plain_arena'),
            'apiBase' => rtrim($request->getSchemeAndHttpHost().'/api', '/'),
            'page' => 'tokens',
        ]);
    }

    /** Create (or rotate) the device token for one arena. */
    public function store(Request $request, Arena $arena): RedirectResponse
    {
        $event = $this->event();

        abort_unless($event && $arena->event_id === $event->id, 403);

        $token = DB::transaction(function () use ($arena) {
            $user = $arena->apiUser;

            if (! $user) {
                $user = User::create([
                    'name' => "Perangkat {$arena->name}",
                    'email' => 'arena-'.Str::lower(Str::random(10)).'@device.nanorobotic.test',
                    'password' => Str::password(32),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $user->syncRoles([RoleEnum::ArenaOperator->value]);

                $arena->update(['api_user_id' => $user->id]);
            }

            // One active token per arena: rotating invalidates the old one.
            $user->tokens()->delete();

            $plain = $user->createToken(
                $arena->code ?: $arena->name,
                [EnsureArenaDevice::ABILITY],
            )->plainTextToken;

            $arena->update(['token_issued_at' => now()]);

            $arena->writeAudit('updated', null, ['token_issued_at' => now()->toIso8601String()], 'Token perangkat arena diterbitkan');

            return $plain;
        });

        return redirect()->route('eventadmin.tokens.index')
            ->with('status', "Token untuk {$arena->name} dibuat. Salin sekarang, tidak akan ditampilkan lagi.")
            ->with('plain_token', $token)
            ->with('plain_arena', $arena->name);
    }

    /** Revoke the arena token without deleting the service account. */
    public function destroy(Request $request, Arena $arena): RedirectResponse
    {
        $event = $this->event();

        abort_unless($event && $arena->event_id === $event->id, 403);

        $arena->apiUser?->tokens()->delete();

        $arena->update(['token_issued_at' => null]);

        $arena->writeAudit('updated', null, ['token_issued_at' => null], 'Token perangkat arena dicabut');

        return redirect()->route('eventadmin.tokens.index')
            ->with('status', "Token {$arena->name} dicabut. Perangkat tidak dapat mengirim waktu lagi.");
    }

    private function event(): ?Event
    {
        return Event::with('categories')
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first()
            ?? Event::latest('starts_at')->first();
    }
}
