<?php

namespace App\Http\Controllers\EventAdmin;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\ArenaAssignment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Event Admin staff page: judge panel size per level, active arena
 * assignments and the quorum each panel produces. Assignments are managed
 * here (not only in the Filament panel) because they drive which arena an
 * operator or judge can act on.
 */
class StaffController extends Controller
{
    /** Assignment role => the platform role a user must hold to get it. */
    private const ASSIGNABLE = [
        'operator' => 'arena_operator',
        'judge' => 'judge',
    ];

    public function __invoke(Request $request): View
    {
        $event = $this->event();

        $assignments = ArenaAssignment::with(['user', 'arena'])
            ->whereHas('arena', fn ($q) => $event ? $q->where('event_id', $event->id) : $q)
            ->latest()
            ->get();

        $panelSize = $assignments->where('role', 'judge')->count();
        $quorum = intdiv($panelSize, 2) + 1;

        return view('event-admin.staff', [
            'event' => $event,
            'activities' => $assignments,
            'panelSize' => $panelSize,
            'quorum' => $quorum,
            'judges' => User::role('judge')->orderBy('name')->get(),
            'operators' => User::role('arena_operator')->orderBy('name')->get(),
            'chiefs' => User::role('chief_judge')->get(),
            'arenas' => $event ? Arena::where('event_id', $event->id)->orderBy('name')->get() : collect(),
            'page' => 'staff',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $event = $this->event();

        abort_unless($event, 404, 'Belum ada event aktif.');

        $validated = $request->validate([
            'arena_id' => ['required', 'exists:arenas,id'],
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', Rule::in(array_keys(self::ASSIGNABLE))],
        ]);

        $arena = Arena::findOrFail($validated['arena_id']);

        abort_unless($arena->event_id === $event->id, 403, 'Arena ini bukan bagian dari event aktif.');

        $user = User::findOrFail($validated['user_id']);

        abort_unless(
            $user->hasRole(self::ASSIGNABLE[$validated['role']]),
            422,
            'Pengguna ini tidak memiliki peran tersebut.',
        );

        ArenaAssignment::firstOrCreate(
            [
                'arena_id' => $arena->id,
                'user_id' => $user->id,
                'role' => $validated['role'],
            ],
            [
                'uuid' => (string) Str::uuid(),
                'is_active' => true,
            ],
        );

        return back()->with('status', "{$user->name} ditugaskan sebagai {$validated['role']} di {$arena->name}.");
    }

    public function toggle(Request $request, ArenaAssignment $assignment): RedirectResponse
    {
        $event = $this->event();

        abort_unless($event && $assignment->arena?->event_id === $event->id, 403);

        $assignment->update(['is_active' => ! $assignment->is_active]);

        return back()->with('status', 'Status penugasan diperbarui.');
    }

    public function destroy(Request $request, ArenaAssignment $assignment): RedirectResponse
    {
        $event = $this->event();

        abort_unless($event && $assignment->arena?->event_id === $event->id, 403);

        $assignment->delete();

        return back()->with('status', 'Penugasan dihapus.');
    }

    private function event(): ?Event
    {
        return Event::with('categories')
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first()
            ?? Event::with('categories')->latest('starts_at')->first();
    }
}
