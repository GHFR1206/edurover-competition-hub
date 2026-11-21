<?php

namespace App\Http\Controllers\EventAdmin;

use App\Enums\CategoryStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Rulebook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Event Admin setup wizard: categories, rulesets and challenges.
 * Ruleset/challenge content is edited in the Filament panel, but the category
 * lifecycle up to "all runs finished" is driven from this page. Publishing is
 * reserved for the Chief Judge (PublicationService).
 */
class SetupController extends Controller
{
    /** Statuses the event admin may set directly. */
    private const EDITABLE_STATUSES = [
        CategoryStatus::Registration,
        CategoryStatus::Scheduled,
        CategoryStatus::Running,
        CategoryStatus::AllRunsFinished,
    ];

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
            'editableStatuses' => self::EDITABLE_STATUSES,
            'page' => 'setup',
        ]);
    }

    /** Advance a category through the pre-publication lifecycle. */
    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(CategoryStatus::class)],
        ]);

        $status = CategoryStatus::from($validated['status']);

        abort_unless(in_array($status, self::EDITABLE_STATUSES, true), 403, 'Publikasi kategori dilakukan oleh Chief Judge.');

        if ($category->isPublished()) {
            return back()->withErrors([
                'status' => 'Kategori yang sudah dipublikasikan tidak dapat diubah lagi.',
            ]);
        }

        $category->update(['status' => $status]);

        return back()->with('status', "Kategori {$category->level->publicLabel()} diubah ke {$status->label()}.");
    }

    /**
     * Choose how a category is ranked: judge points or arena timing.
     * Locked once results are published, because changing it would rewrite
     * an official podium.
     */
    public function updateScoringMode(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'scoring_mode' => ['required', 'in:jury,time'],
        ]);

        if ($category->isPublished()) {
            return back()->withErrors([
                'scoring_mode' => 'Mode penilaian tidak dapat diubah setelah hasil dipublikasikan.',
            ]);
        }

        $category->update(['scoring_mode' => $validated['scoring_mode']]);

        $label = $validated['scoring_mode'] === 'time' ? 'Waktu arena' : 'Skor juri';

        return back()->with('status', "Mode penilaian {$category->level->publicLabel()} diubah ke {$label}.");
    }
}
