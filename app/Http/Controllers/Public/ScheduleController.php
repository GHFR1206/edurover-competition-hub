<?php

namespace App\Http\Controllers\Public;

use App\Enums\Level;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Run;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public schedule (Jadwal). Lists runs per level with their arena and status.
 * Only team/school names are shown; participants stay private.
 */
class ScheduleController extends Controller
{
    public function __invoke(Request $request): View
    {
        $event = Event::with('categories')
            ->whereIn('status', ['running', 'scheduled', 'finished'])
            ->inDisplayOrder()
            ->latest('starts_at')
            ->first()
            ?? Event::with('categories')->latest()->first();

        $categoryIds = $event?->categories->pluck('id') ?? collect();

        $activeLevel = Level::fromAlias($request->query('level')) ?? Level::Smp;

        $runs = Run::with(['team.school', 'arena', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->whereHas('category', fn ($q) => $q->where('level', $activeLevel->value))
            ->orderBy('scheduled_at')
            ->get();

        $todayRuns = Run::whereIn('category_id', $categoryIds)
            ->whereDate('scheduled_at', now()->toDateString())
            ->count();

        $activeArenas = $event?->arenas()->where('is_active', true)->count() ?? 0;
        $next = $runs->firstWhere(fn ($run) => $run->scheduled_at && $run->scheduled_at->isFuture())
            ?? $runs->first();

        return view('public.schedule', [
            'event' => $event,
            'activeLevel' => $activeLevel,
            'runs' => $runs,
            'todayRuns' => $todayRuns,
            'activeArenas' => $activeArenas,
            'next' => $next,
            'liveEvent' => $event?->id,
            'page' => 'jadwal',
        ]);
    }
}
