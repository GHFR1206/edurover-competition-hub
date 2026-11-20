<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Team;
use App\Services\StandingsService;
use Illuminate\View\View;

/**
 * Public landing page (Beranda). Shows the live event, headline stats and
 * a leaderboard teaser. Never leaks unpublished values or personal data.
 */
class LandingController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function __invoke(): View
    {
        $event = $this->activeEvent();

        $categories = $event
            ? $event->categories()->orderBy('level')->get()
            : collect();

        $teamCount = $event ? Team::whereIn('category_id', $categories->pluck('id'))->count() : 0;
        $arenaCount = $event ? Arena::where('event_id', $event->id)->count() : 0;

        // Featured standings: the first category that already has approved scores,
        // falling back to the middle level so the teaser is never empty.
        $featured = $categories->first(fn (Category $c) => $this->standings->hasApprovedScores($c))
            ?? $categories->firstWhere('level.value', 'smp')
            ?? $categories->first();

        $featuredStandings = $featured ? $this->standings->forCategory($featured)->take(3) : collect();

        return view('public.landing', [
            'event' => $event,
            'categories' => $categories,
            'teamCount' => $teamCount,
            'arenaCount' => $arenaCount,
            'publishedCount' => $categories->filter(fn (Category $c) => $c->isPublished())->count(),
            'schoolCount' => $event
                ? Team::whereIn('category_id', $categories->pluck('id'))->distinct('school_id')->count('school_id')
                : 0,
            'featured' => $featured,
            'featuredStandings' => $featuredStandings,
            'page' => 'beranda',
        ]);
    }

    private function activeEvent(): ?Event
    {
        return Event::with(['categories' => fn ($q) => $q->orderBy('level')])
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first()
            ?? Event::with('categories')->latest()->first();
    }
}
