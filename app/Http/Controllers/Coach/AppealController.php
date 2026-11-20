<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\Run;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Coach appeals (banding). A coach may appeal a run belonging to their own
 * school's team, including a mandatory reason. Appeals are append-only from
 * the coach side; only the Chief Judge resolves them.
 */
class AppealController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = $request->user()->coachProfile?->school_id;

        $appeals = Appeal::with(['run.arena', 'run.category', 'team'])
            ->whereHas('team', fn ($q) => $q->where('school_id', $schoolId))
            ->latest()
            ->get();

        $teams = Team::with(['category', 'runs.arena'])
            ->where('school_id', $schoolId)
            ->get();

        return view('coach.appeals.index', [
            'appeals' => $appeals,
            'teams' => $teams,
            'page' => 'appeals',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = $request->user()->coachProfile?->school_id;

        $validated = $request->validate([
            'run_id' => ['required', 'exists:runs,id'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $run = Run::with('team')->findOrFail($validated['run_id']);

        abort_unless($run->team?->school_id === $schoolId, 403, 'Run ini bukan milik tim sekolah Anda.');

        // A coach cannot have two open appeals for the same run.
        $open = Appeal::where('run_id', $run->id)
            ->whereIn('status', ['submitted', 'under_review'])
            ->exists();

        if ($open) {
            return back()->withErrors(['reason' => 'Sudah ada banding yang sedang diproses untuk run ini.']);
        }

        Appeal::create([
            'uuid' => (string) Str::uuid(),
            'run_id' => $run->id,
            'team_id' => $run->team_id,
            'submitted_by' => $request->user()->id,
            'reason' => $validated['reason'],
            'status' => 'submitted',
        ]);

        return back()->with('status', 'Banding terkirim. Chief Judge akan meninjau.');
    }
}
