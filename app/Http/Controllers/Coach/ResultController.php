<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Coach view of their own teams' scores. Only Submitted and Approved scores
 * are shown; Draft is never revealed.
 */
class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = $request->user()->coachProfile?->school_id;

        $teams = Team::with(['school', 'category'])
            ->where('school_id', $schoolId)
            ->get();

        return view('coach.results.index', ['teams' => $teams]);
    }

    public function show(Request $request, Team $team): View
    {
        abort_unless($request->user()->coachProfile?->school_id === $team->school_id, 403);

        $team->load(['school', 'category', 'runs.arena', 'runs.scores.lines.scoreObject']);

        $visibleRuns = $team->runs->map(function ($run) {
            $run->setRelation('scores', $run->scores
                ->filter(fn ($score) => $score->status->isVisibleToTeam())
                ->values());

            return $run;
        });

        return view('coach.results.show', [
            'team' => $team,
            'runs' => $visibleRuns,
        ]);
    }
}
