<?php

namespace App\Http\Controllers\Public;

use App\Enums\Level;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public leaderboard (Leaderboard). Shows every category's standings.
 * A row is "Resmi" only once the category is published by the Chief Judge;
 * everything else is shown as "Sementara" (provisional) and labelled so.
 */
class LeaderboardController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function __invoke(Request $request): View
    {
        $event = Event::with('categories')
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->orderByRaw("FIELD(status, 'running', 'scheduled', 'finished')")
            ->latest('starts_at')
            ->first()
            ?? Event::with('categories')->latest()->first();

        $activeLevel = Level::fromAlias($request->query('level')) ?? Level::Sd;

        $category = $event?->categories->firstWhere('level', $activeLevel);

        $standings = $category ? $this->standings->forCategory($category) : collect();
        $published = $category?->isPublished() ?? false;

        return view('public.leaderboard', [
            'event' => $event,
            'activeLevel' => $activeLevel,
            'category' => $category,
            'standings' => $standings,
            'published' => $published,
            'page' => 'leaderboard',
        ]);
    }
}
