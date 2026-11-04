<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Event;
use App\Models\Team;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $event = Event::with(['categories' => fn ($q) => $q->orderBy('level')])
            ->whereIn('status', ['scheduled', 'running', 'finished'])
            ->latest('starts_at')
            ->first()
            ?? Event::with('categories')->latest()->first();

        return view('public.landing', [
            'event' => $event,
            'categories' => $event?->categories ?? collect(),
            'arenaCount' => $event ? Arena::where('event_id', $event->id)->count() : 0,
            'teamCount' => $event ? Team::whereIn('category_id', $event->categories->pluck('id'))->count() : 0,
        ]);
    }
}
