<?php

namespace App\Http\Controllers\EventAdmin;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Event;
use App\Models\HeatSlot;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Event Admin scheduling: list sessions, create a run with arena/team
 * conflict checks (unique per arena+time and per team+time).
 */
class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $event = Event::with(['categories', 'arenas'])->latest('starts_at')->first();

        $runs = Run::with(['team.school', 'arena', 'category'])
            ->when($event, fn ($q) => $q->where('event_id', $event->id))
            ->orderBy('scheduled_at')
            ->get();

        $arenaFilter = $request->integer('arena');

        if ($arenaFilter) {
            $runs = $runs->where('arena_id', $arenaFilter);
        }

        return view('event-admin.schedule', [
            'event' => $event,
            'runs' => $runs,
            'arenas' => $event?->arenas ?? collect(),
            'teams' => $event
                ? Team::with('category')->whereIn('category_id', $event->categories->pluck('id'))->orderBy('name')->get()
                : collect(),
            'arenaFilter' => $arenaFilter,
            'page' => 'schedule',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'arena_id' => ['required', 'exists:arenas,id'],
            'scheduled_at' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $team = Team::findOrFail($validated['team_id']);

        // Conflict checks: same arena+time or same team+time.
        $conflict = Run::where('scheduled_at', $validated['scheduled_at'])
            ->where(fn ($q) => $q->where('arena_id', $validated['arena_id'])->orWhere('team_id', $team->id))
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'scheduled_at' => 'Sesi bentrok: arena atau tim yang sama sudah terjadwal pada waktu tersebut.',
            ])->withInput();
        }

        $duration = (int) ($validated['duration'] ?? 15);
        $arena = Arena::findOrFail($validated['arena_id']);
        $startsAt = Carbon::parse($validated['scheduled_at']);

        // Each new session for a team is the next attempt (re-runs included).
        $attemptNo = (int) Run::where('category_id', $team->category_id)
            ->where('team_id', $team->id)
            ->max('attempt_no') + 1;

        $slot = HeatSlot::firstOrCreate(
            ['arena_id' => $arena->id, 'category_id' => $team->category_id, 'starts_at' => $startsAt],
            [
                'slot_no' => (HeatSlot::where('arena_id', $arena->id)->where('category_id', $team->category_id)->max('slot_no') ?? 0) + 1,
                'label' => 'Heat '.$team->category?->level->publicLabel(),
                'ends_at' => $startsAt->copy()->addMinutes($duration),
                'status' => 'scheduled',
            ],
        );

        Run::create([
            'event_id' => $team->category->event_id,
            'category_id' => $team->category_id,
            'arena_id' => $arena->id,
            'team_id' => $team->id,
            'heat_slot_id' => $slot->id,
            'attempt_no' => $attemptNo,
            'status' => 'scheduled',
            'scheduled_at' => $startsAt,
        ]);

        return redirect()->route('eventadmin.schedule.index')
            ->with('status', "Sesi {$team->name} ditambahkan pada {$startsAt->format('H:i')} WIB. Tidak ada bentrok arena atau tim.");
    }
}
