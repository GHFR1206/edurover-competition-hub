<?php

namespace App\Http\Controllers\EventAdmin;

use App\Enums\Level;
use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\ArenaAssignment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Event Admin staff page: judge panel size per level, active arena
 * assignments and the quorum each panel produces.
 */
class StaffController extends Controller
{
    public function __invoke(Request $request): View
    {
        $event = Event::with('categories')->latest('starts_at')->first();

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
            'judges' => User::role('judge')->get(),
            'operators' => User::role('arena_operator')->get(),
            'chiefs' => User::role('chief_judge')->get(),
            'arenas' => $event ? Arena::where('event_id', $event->id)->get() : collect(),
            'page' => 'staff',
        ]);
    }
}
