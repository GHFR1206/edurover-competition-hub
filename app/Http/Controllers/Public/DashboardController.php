<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Run;
use App\Models\Team;
use App\Services\StandingsService;
use Illuminate\View\View;

/**
 * Home base for the "Publik" role after login. It reuses the public site's
 * data (active event, categories, schedule, standings) and adds shortcuts so a
 * signed-in spectator can jump straight to the page they care about.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function __invoke(): View
    {
        $event = $this->activeEvent();

        $categories = $event
            ? $event->categories()->orderBy('level')->get()
            : collect();

        $categoryIds = $categories->pluck('id');

        $teamCount = $event ? Team::whereIn('category_id', $categoryIds)->count() : 0;

        $schoolCount = $event
            ? Team::whereIn('category_id', $categoryIds)->distinct('school_id')->count('school_id')
            : 0;

        $arenaCount = $event ? Arena::where('event_id', $event->id)->where('is_active', true)->count() : 0;

        $upcomingRuns = $event
            ? Run::with(['team.school', 'arena', 'category'])
                ->whereIn('category_id', $categoryIds)
                ->whereIn('status', ['scheduled', 'called', 'ready', 'running'])
                ->whereNotNull('scheduled_at')
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get()
            : collect();

        // Same rule as the landing page: prefer a category that already has
        // approved scores so the teaser is never empty.
        $featured = $categories->first(fn (Category $c) => $this->standings->hasApprovedScores($c))
            ?? $categories->firstWhere('level.value', 'smp')
            ?? $categories->first();

        $featuredStandings = $featured ? $this->standings->forCategory($featured)->take(3) : collect();

        return view('public.dashboard', [
            'event' => $event,
            'categories' => $categories,
            'teamCount' => $teamCount,
            'schoolCount' => $schoolCount,
            'arenaCount' => $arenaCount,
            'publishedCount' => $categories->filter(fn (Category $c) => $c->isPublished())->count(),
            'upcomingRuns' => $upcomingRuns,
            'featured' => $featured,
            'featuredStandings' => $featuredStandings,
            'liveEvent' => $event?->id,
            'page' => 'dashboard',
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
