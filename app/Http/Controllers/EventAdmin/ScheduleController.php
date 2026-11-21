<?php

namespace App\Http\Controllers\EventAdmin;

use App\Enums\RunStatus;
use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\HeatSlot;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Event Admin scheduling: list sessions, create/update/cancel runs with
 * arena/team conflict checks. Only runs that have not started yet may be
 * moved or cancelled, so the arena operators keep a stable queue.
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'arena_id' => ['required', 'exists:arenas,id'],
            'scheduled_at' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $team = Team::findOrFail($validated['team_id']);
        $startsAt = Carbon::parse($validated['scheduled_at']);
        $duration = (int) ($validated['duration'] ?? 15);

        if ($this->conflict($validated['arena_id'], $team->id, $startsAt)) {
            return back()->withErrors([
                'scheduled_at' => 'Sesi bentrok: arena atau tim yang sama sudah terjadwal pada waktu tersebut.',
            ])->withInput();
        }

        $arena = Arena::findOrFail($validated['arena_id']);

        // Each new session for a team is the next attempt (re-runs included).
        $attemptNo = (int) Run::where('category_id', $team->category_id)
            ->where('team_id', $team->id)
            ->max('attempt_no') + 1;

        $slot = $this->slotFor($arena, $team->category, $startsAt, $duration);

        Run::create([
            'event_id' => $team->category->event_id,
            'category_id' => $team->category_id,
            'arena_id' => $arena->id,
            'team_id' => $team->id,
            'heat_slot_id' => $slot->id,
            'attempt_no' => $attemptNo,
            'status' => RunStatus::Scheduled,
            'scheduled_at' => $startsAt,
        ]);

        return redirect()->route('eventadmin.schedule.index')
            ->with('status', "Sesi {$team->name} ditambahkan pada {$startsAt->format('H:i')} WIB. Tidak ada bentrok arena atau tim.");
    }

    /** Move a not-yet-started session to another time and/or arena. */
    public function update(Request $request, Run $run): RedirectResponse
    {
        if ($run->status !== RunStatus::Scheduled) {
            return back()->withErrors([
                'scheduled_at' => 'Sesi yang sudah dipanggil atau berjalan tidak dapat dijadwalkan ulang.',
            ]);
        }

        $validated = $request->validate([
            'arena_id' => ['required', 'exists:arenas,id'],
            'scheduled_at' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $arena = Arena::findOrFail($validated['arena_id']);
        $startsAt = Carbon::parse($validated['scheduled_at']);
        $duration = (int) ($validated['duration'] ?? 15);

        if ($arena->event_id !== $run->event_id) {
            return back()->withErrors(['arena_id' => 'Arena ini bukan bagian dari event yang sama.']);
        }

        if ($this->conflict($arena->id, $run->team_id, $startsAt, $run->id)) {
            return back()->withErrors([
                'scheduled_at' => 'Sesi bentrok: arena atau tim yang sama sudah terjadwal pada waktu tersebut.',
            ])->withInput();
        }

        $oldSlot = $run->heatSlot;
        $slot = $this->slotFor($arena, $run->category, $startsAt, $duration);

        $run->update([
            'arena_id' => $arena->id,
            'heat_slot_id' => $slot->id,
            'scheduled_at' => $startsAt,
        ]);

        $this->pruneSlot($oldSlot);

        return redirect()->route('eventadmin.schedule.index')
            ->with('status', "Sesi {$run->team?->name} dipindahkan ke {$arena->name} pada {$startsAt->format('d M Y, H:i')} WIB.");
    }

    /** Cancel a session that has not started and has no scores yet. */
    public function destroy(Run $run): RedirectResponse
    {
        if ($run->status !== RunStatus::Scheduled) {
            return back()->withErrors([
                'run' => 'Sesi yang sudah dipanggil atau berjalan tidak dapat dibatalkan.',
            ]);
        }

        if ($run->scores()->exists()) {
            return back()->withErrors([
                'run' => 'Sesi ini sudah memiliki skor dan tidak dapat dibatalkan.',
            ]);
        }

        $slot = $run->heatSlot;
        $label = $run->team?->name ?? 'Sesi';

        $run->delete();
        $this->pruneSlot($slot);

        return redirect()->route('eventadmin.schedule.index')
            ->with('status', "Sesi {$label} dibatalkan.");
    }

    /** Same arena+time or same team+time already booked (optionally ignoring one run). */
    private function conflict(int $arenaId, int $teamId, Carbon $startsAt, ?int $ignoreRunId = null): bool
    {
        return Run::where('scheduled_at', $startsAt)
            ->when($ignoreRunId, fn ($q) => $q->whereKeyNot($ignoreRunId))
            ->where(fn ($q) => $q->where('arena_id', $arenaId)->orWhere('team_id', $teamId))
            ->exists();
    }

    /** Reuse the matching heat slot or create the next one for the arena. */
    private function slotFor(Arena $arena, Category $category, Carbon $startsAt, int $duration): HeatSlot
    {
        return HeatSlot::firstOrCreate(
            ['arena_id' => $arena->id, 'category_id' => $category->id, 'starts_at' => $startsAt],
            [
                // slot_no is unique per arena (not per category), so it must be
                // allocated across every category scheduled in that arena.
                'slot_no' => (HeatSlot::where('arena_id', $arena->id)->max('slot_no') ?? 0) + 1,
                'label' => 'Heat '.$category->level->publicLabel(),
                'ends_at' => $startsAt->copy()->addMinutes($duration),
                'status' => 'scheduled',
            ],
        );
    }

    /** Remove a heat slot that no longer holds any run. */
    private function pruneSlot(?HeatSlot $slot): void
    {
        if ($slot && ! $slot->runs()->exists()) {
            $slot->delete();
        }
    }
}
