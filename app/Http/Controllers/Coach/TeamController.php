<?php

namespace App\Http\Controllers\Coach;

use App\Enums\ScoreStatus;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function dashboard(Request $request): View
    {
        $teams = $this->coachTeams($request);

        return view('coach.dashboard', [
            'teams' => $teams,
            'pending' => $teams->sum(fn (Team $t) => $t->runs->sum(
                fn ($r) => $r->scores->where('status', ScoreStatus::Submitted)->count()
            )),
        ]);
    }

    public function index(Request $request): View
    {
        return view('coach.teams.index', ['teams' => $this->coachTeams($request)]);
    }

    public function show(Request $request, Team $team): View
    {
        abort_unless($this->owns($request, $team), 403);

        $team->load(['school', 'category.event', 'participants', 'runs.arena', 'runs.scores']);

        return view('coach.teams.show', ['team' => $team]);
    }

    /** @return Collection<int, Team> */
    private function coachTeams(Request $request): Collection
    {
        $schoolId = $request->user()->coachProfile?->school_id;

        if (! $schoolId) {
            return collect();
        }

        return Team::with(['school', 'category', 'runs.scores'])
            ->where('school_id', $schoolId)
            ->get();
    }

    private function owns(Request $request, Team $team): bool
    {
        return $request->user()->coachProfile?->school_id === $team->school_id;
    }
}
