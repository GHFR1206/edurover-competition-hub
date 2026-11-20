<?php

namespace App\Http\Controllers\EventAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Event;
use App\Models\Rulebook;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Event Admin setup wizard: categories, rulesets and challenges.
 * Read-only overview here; edits happen in the Filament panel.
 */
class SetupController extends Controller
{
    public function index(Request $request): View
    {
        $event = Event::with([
            'categories' => fn ($q) => $q->orderBy('level'),
            'categories.challenges',
            'categories.rulebooks',
            'categories.capabilityTags',
        ])->latest('starts_at')->first();

        $categories = $event?->categories ?? collect();

        $checks = [
            ['Kategori kompetisi sudah dipilih', $categories->isNotEmpty()],
            ['Ruleset tiap level sudah dikunci', $categories->every(fn (Category $c) => $c->rulebooks->contains(fn (Rulebook $r) => $r->locked_at !== null))],
            ['Semua challenge lengkap', $categories->every(fn (Category $c) => $c->challenges->isNotEmpty())],
        ];

        return view('event-admin.setup', [
            'event' => $event,
            'categories' => $categories,
            'checks' => $checks,
            'page' => 'setup',
        ]);
    }
}
