<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Team;
use Illuminate\View\View;

/**
 * Public event overview (Event). Describes the current event and the three
 * level categories. Registration CTA points at the auth flow.
 */
class EventController extends Controller
{
    public function __invoke(): View
    {
        $event = Event::with([
            'categories' => fn ($q) => $q->orderBy('level'),
            'arenas',
        ])
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first()
            ?? Event::with(['categories', 'arenas'])->latest()->first();

        $categories = $event?->categories ?? collect();

        $teamCounts = $event
            ? Team::whereIn('category_id', $categories->pluck('id'))
                ->selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')
                ->pluck('total', 'category_id')
            : collect();

        return view('public.events', [
            'event' => $event,
            'categories' => $categories,
            'teamCounts' => $teamCounts,
            'page' => 'event',
        ]);
    }
}
