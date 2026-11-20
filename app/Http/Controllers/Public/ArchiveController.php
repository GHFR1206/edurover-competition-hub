<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public archive (Arsip Hasil). Past (finished/archived) events, grouped by
 * year, with the champion of each published category.
 */
class ArchiveController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function __invoke(Request $request): View
    {
        $years = Event::query()
            ->whereNotNull('starts_at')
            ->orderByDesc('starts_at')
            ->pluck('starts_at')
            ->map(fn ($date) => (int) $date->format('Y'))
            ->unique()
            ->values();

        $activeYear = (int) ($request->query('year') ?: ($years->first() ?? now()->year));

        if (! $years->contains($activeYear)) {
            $years = $years->push($activeYear)->sortDesc()->values();
        }

        $events = Event::with('categories')
            ->whereIn('status', ['finished', 'archived'])
            ->whereYear('starts_at', $activeYear)
            ->orderByDesc('starts_at')
            ->get();

        $archive = $events->map(function (Event $event) {
            $champions = $event->categories
                ->filter(fn (Category $c) => $c->isPublished())
                ->sortBy('level')
                ->map(fn (Category $category) => [
                    'category' => $category,
                    'rows' => $this->standings->forCategory($category)->take(3),
                ]);

            return ['event' => $event, 'champions' => $champions];
        });

        return view('public.archive', [
            'years' => $years,
            'activeYear' => $activeYear,
            'archive' => $archive,
            'page' => 'arsip',
        ]);
    }
}
