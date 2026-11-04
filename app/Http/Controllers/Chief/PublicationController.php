<?php

namespace App\Http\Controllers\Chief;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\PublicationService;
use App\Services\StandingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Chief Judge publication step — separate from approve. Blocked until every
 * run is settled and every score approved; blocking reasons are always shown.
 */
class PublicationController extends Controller
{
    public function __construct(
        private readonly PublicationService $publication,
        private readonly StandingsService $standings,
    ) {}

    public function index(Request $request): View
    {
        $categories = Category::with('event')->orderBy('level')->get()->map(fn (Category $c) => [
            'category' => $c,
            'reasons' => $this->publication->blockingReasons($c),
            'publishable' => $this->publication->canPublish($c),
            'standings' => $this->standings->forCategory($c),
        ]);

        return view('chief.publication.index', ['categories' => $categories]);
    }

    public function publish(Request $request, Category $category): RedirectResponse
    {
        $reasons = $this->publication->publish($category, $request->user());

        if ($reasons !== []) {
            return back()->withErrors(['publish' => $reasons]);
        }

        return back()->with('status', "Kategori {$category->name} dipublikasikan.");
    }
}
