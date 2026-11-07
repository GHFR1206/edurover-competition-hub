<?php

namespace App\Http\Controllers\Chief;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Services\PublicationService;
use App\Services\StandingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Chief Judge publication step — separate from approve. Blocked until every
 * run is settled and every score approved; blocking reasons are always shown.
 *
 * Scoped to ONE event at a time so categories from different events never mix.
 */
class PublicationController extends Controller
{
    public function __construct(
        private readonly PublicationService $publication,
        private readonly StandingsService $standings,
    ) {}

    public function index(Request $request): View
    {
        $events = $this->events($request);
        $event = $this->resolveEvent($request, $events);

        $categories = $event
            ? Category::with('event')
                ->where('event_id', $event->id)
                ->orderBy('level')
                ->get()
                ->map(fn (Category $c) => [
                    'category' => $c,
                    'reasons' => $this->publication->blockingReasons($c),
                    'publishable' => $this->publication->canPublish($c),
                    'standings' => $this->standings->forCategory($c),
                ])
            : collect();

        return view('chief.publication.index', [
            'events' => $events,
            'event' => $event,
            'categories' => $categories,
        ]);
    }

    public function publish(Request $request, Category $category): RedirectResponse
    {
        if (! $this->events($request)->contains('id', $category->event_id)) {
            abort(403, 'Kategori ini bukan bagian dari event yang dapat Anda kelola.');
        }

        $reasons = $this->publication->publish($category, $request->user());

        if ($reasons !== []) {
            return back()->withErrors(['publish' => $reasons]);
        }

        return redirect()
            ->route('chief.publication.index', ['event' => $category->event_id])
            ->with('status', "Kategori {$category->name} dipublikasikan.");
    }

    /** @return Collection<int, Event> */
    private function events(Request $request): Collection
    {
        return Event::orderByDesc('starts_at')->get();
    }

    private function resolveEvent(Request $request, Collection $events): ?Event
    {
        $requested = $request->integer('event');

        if ($requested && $events->contains('id', $requested)) {
            return $events->firstWhere('id', $requested);
        }

        // Default: the event currently running, else the next scheduled, else the latest.
        return $events->firstWhere('status', 'running')
            ?? $events->firstWhere('status', 'scheduled')
            ?? $events->first();
    }
}
