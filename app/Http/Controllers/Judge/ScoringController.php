<?php

namespace App\Http\Controllers\Judge;

use App\Enums\ScoreStatus;
use App\Http\Controllers\Controller;
use App\Models\Run;
use App\Models\Score;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Judge scoring flow. A judge only sees runs on arenas they are assigned to,
 * and only their own authored scores.
 */
class ScoringController extends Controller
{
    public function __construct(private readonly ScoringService $scoring) {}

    public function dashboard(Request $request): View
    {
        $tasks = $this->tasks($request);

        return view('judge.dashboard', [
            'pending' => $tasks->filter(fn (Run $r) => $r->status->isSettled())->count(),
            'assigned' => $tasks->count(),
            'submitted' => Score::where('judge_id', $request->user()->id)
                ->where('status', ScoreStatus::Submitted->value)->count(),
        ]);
    }

    public function index(Request $request): View
    {
        return view('judge.tasks.index', ['runs' => $this->tasks($request)]);
    }

    public function edit(Request $request, Run $run): View
    {
        $this->authorizeRun($request, $run);

        $run->load(['team.school', 'arena', 'category.challenges.scoreObjects.capabilityTag']);

        $challenge = $run->category->challenges->first();
        $score = Score::with('lines')
            ->where('run_id', $run->id)
            ->where('judge_id', $request->user()->id)
            ->first();

        $lines = $score?->lines->keyBy('score_object_id') ?? collect();

        return view('judge.scores.edit', [
            'run' => $run,
            'challenge' => $challenge,
            'score' => $score,
            'lines' => $lines,
        ]);
    }

    public function store(Request $request, Run $run): RedirectResponse
    {
        $this->authorizeRun($request, $run);

        $validated = $request->validate([
            'entries' => ['array'],
            'entries.*.score_object_id' => ['required', 'integer'],
            'entries.*.quantity' => ['nullable', 'integer', 'min:0'],
            'entries.*.achieved' => ['nullable', 'boolean'],
            'entries.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->scoring->saveDraft($run, $request->user(), $validated['entries'] ?? []);

        return redirect()
            ->route('judge.scores.edit', $run)
            ->with('status', 'Draft skor tersimpan.');
    }

    public function submit(Request $request, Score $score): RedirectResponse
    {
        abort_unless($score->judge_id === $request->user()->id, 403);
        abort_if($score->status === ScoreStatus::Approved, 403);

        $this->scoring->submit($score, $request->user());

        return redirect()
            ->route('judge.scores.mine')
            ->with('status', 'Skor diajukan ke Chief Judge (belum resmi).');
    }

    public function mine(Request $request): View
    {
        $scores = Score::with(['run.team.school', 'run.arena'])
            ->where('judge_id', $request->user()->id)
            ->latest()
            ->get();

        return view('judge.scores.mine', ['scores' => $scores]);
    }

    /** Runs the judge is allowed to score. */
    private function tasks(Request $request): Collection
    {
        $arenaIds = $request->user()->arenaAssignments()
            ->where('role', 'judge')
            ->where('is_active', true)
            ->pluck('arena_id');

        if ($arenaIds->isEmpty()) {
            return collect();
        }

        return Run::with(['team.school', 'arena', 'category', 'scores'])
            ->whereIn('arena_id', $arenaIds)
            ->orderByDesc('scheduled_at')
            ->get();
    }

    private function authorizeRun(Request $request, Run $run): void
    {
        $assigned = $request->user()->arenaAssignments()
            ->where('arena_id', $run->arena_id)
            ->where('role', 'judge')
            ->where('is_active', true)
            ->exists();

        abort_unless($assigned, 403, 'Anda tidak ditugaskan di arena ini.');
    }
}
