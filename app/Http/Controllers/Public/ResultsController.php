<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Services\StandingsService;
use Illuminate\View\View;

/**
 * Public results are only ever visible AFTER a category is published.
 * Draft values never leak; NISN is never rendered.
 */
class ResultsController extends Controller
{
    public function __construct(private readonly StandingsService $standings) {}

    public function index(): View
    {
        $event = Event::with('categories')->latest('starts_at')->first();

        return view('public.results-index', [
            'event' => $event,
            'categories' => $event?->categories->sortBy('level') ?? collect(),
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless($category->isPublished(), 404);

        return view('public.results-category', [
            'category' => $category,
            'standings' => $this->standings->forCategory($category),
        ]);
    }
}
