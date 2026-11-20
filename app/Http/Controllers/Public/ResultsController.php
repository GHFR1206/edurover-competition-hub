<?php

namespace App\Http\Controllers\Public;

use App\Enums\Level;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Run;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public results (Hasil Resmi). Only published categories are ever visible;
 * draft and provisional values never leak. NISN is never rendered.
 */
class ResultsController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function index(Request $request): View
    {
        $event = Event::with('categories')
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first()
            ?? Event::with('categories')->latest()->first();

        $activeLevel = Level::fromAlias($request->query('level')) ?? Level::Smp;

        $category = $event?->categories->firstWhere('level', $activeLevel);
        $published = $category?->isPublished() ?? false;

        $standings = ($category && $published)
            ? $this->standings->forCategory($category)
            : collect();

        $podium = $standings->take(3)->values();

        $publishedCategories = $event
            ? $event->categories->filter(fn (Category $c) => $c->isPublished())->sortBy('level')
            : collect();

        return view('public.results-index', [
            'event' => $event,
            'activeLevel' => $activeLevel,
            'category' => $category,
            'standings' => $standings,
            'podium' => $podium,
            'published' => $published,
            'publishedCategories' => $publishedCategories,
            'liveEvent' => $event?->id,
            'liveCategory' => $category?->id,
            'page' => 'hasil',
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless($category->isPublished(), 404);

        $category->load(['event', 'challenges.scoreObjects']);

        $standings = $this->standings->forCategory($category);

        return view('public.results-category', [
            'category' => $category,
            'standings' => $standings,
            'podium' => $standings->take(3)->values(),
            'runs' => Run::with(['team.school', 'arena'])
                ->where('category_id', $category->id)
                ->orderBy('scheduled_at')
                ->get(),
            'liveEvent' => $category->event_id,
            'liveCategory' => $category->id,
            'page' => 'hasil',
        ]);
    }
}
