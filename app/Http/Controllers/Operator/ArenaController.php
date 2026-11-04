<?php

namespace App\Http\Controllers\Operator;

use App\Enums\RunStatus;
use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Run;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Arena operator: manage run lifecycle on assigned arenas only.
 */
class ArenaController extends Controller
{
    public function dashboard(Request $request): View
    {
        $arenas = $this->arenas($request);

        return view('operator.dashboard', [
            'arenas' => $arenas,
            'activeRuns' => Run::with(['team.school', 'arena'])
                ->whereIn('arena_id', $arenas->pluck('id'))
                ->whereIn('status', [RunStatus::Called->value, RunStatus::Ready->value, RunStatus::Running->value])
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        return view('operator.arenas.index', ['arenas' => $this->arenas($request)]);
    }

    public function show(Request $request, Arena $arena): View
    {
        abort_unless($this->arenas($request)->contains('id', $arena->id), 403);

        $arena->load(['runs.team.school', 'runs.category']);

        return view('operator.arenas.show', [
            'arena' => $arena,
            'runs' => $arena->runs->sortBy('scheduled_at'),
        ]);
    }

    public function updateRunStatus(Request $request, Run $run): RedirectResponse
    {
        abort_unless($this->arenas($request)->contains('id', $run->arena_id), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:called,ready,running,finished,dnf,dq,no_show,re_run'],
            'outcome_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $status = RunStatus::from($validated['status']);

        $run->status = $status;
        $run->outcome_reason = $validated['outcome_reason'] ?? null;

        match ($status) {
            RunStatus::Called => $run->called_at ??= now(),
            RunStatus::Running => $run->started_at ??= now(),
            default => null,
        };

        if ($status->isSettled()) {
            $run->finished_at = now();

            if ($run->started_at) {
                $run->duration_seconds = $run->started_at->diffInSeconds(now());
            }
        }

        $run->operator_id = $request->user()->id;
        $run->save();

        return back()->with('status', "Status run diperbarui menjadi {$status->label()}.");
    }

    /** @return Collection<int, Arena> */
    private function arenas(Request $request): Collection
    {
        $ids = $request->user()->arenaAssignments()
            ->where('role', 'operator')
            ->where('is_active', true)
            ->pluck('arena_id');

        return Arena::with('event')->whereIn('id', $ids)->get();
    }
}
