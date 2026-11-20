<?php

namespace App\Http\Controllers\EventAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Run;
use App\Models\School;
use App\Models\Team;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Event Admin reports: participation stats plus official-result export
 * gating (only published categories may be exported).
 */
class ReportController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function __invoke(Request $request): View
    {
        $event = Event::with('categories')->latest('starts_at')->first();
        $categoryIds = $event?->categories->pluck('id') ?? collect();

        $published = $event
            ? $event->categories->filter(fn (Category $c) => $c->isPublished())->sortBy('level')
            : collect();

        return view('event-admin.reports', [
            'event' => $event,
            'teamCount' => Team::whereIn('category_id', $categoryIds)->count(),
            'verifiedCount' => Team::whereIn('category_id', $categoryIds)->where('status', 'verified')->count(),
            'reviewCount' => Registration::whereIn('category_id', $categoryIds)->whereIn('status', ['submitted', 'under_review'])->count(),
            'schoolCount' => School::whereHas('teams', fn ($q) => $q->whereIn('category_id', $categoryIds))->count(),
            'runFinished' => Run::whereIn('category_id', $categoryIds)->whereIn('status', ['finished', 'dnf', 'dq', 'no_show'])->count(),
            'runTotal' => Run::whereIn('category_id', $categoryIds)->count(),
            'published' => $published,
            'draftLevels' => $event ? $event->categories->reject(fn (Category $c) => $c->isPublished())->sortBy('level') : collect(),
            'schools' => School::withCount('teams')->orderByDesc('teams_count')->take(8)->get(),
            'page' => 'reports',
        ]);
    }
}
