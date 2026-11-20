<?php

namespace App\Http\Controllers\Chief;

use App\Enums\ScoreStatus;
use App\Http\Controllers\Controller;
use App\Models\ResultApproval;
use App\Models\Score;
use App\Services\CorrectionService;
use App\Services\PublicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/**
 * Chief Judge verification: review, approve, reject, reopen. Maker-checker
 * is enforced: a chief may never approve a score they authored.
 */
class ApprovalController extends Controller
{
    public function __construct(
        private readonly PublicationService $publication,
        private readonly CorrectionService $corrections,
    ) {}

    public function dashboard(Request $request): View
    {
        return view('chief.dashboard', [
            'submitted' => Score::where('status', ScoreStatus::Submitted->value)->count(),
            'reopened' => Score::where('status', ScoreStatus::Reopened->value)->count(),
            'approved' => Score::where('status', ScoreStatus::Approved->value)->count(),
        ]);
    }

    public function index(Request $request): View
    {
        $scores = Score::with(['run.team.school', 'run.arena', 'run.category', 'judge'])
            ->whereIn('status', [ScoreStatus::Submitted->value, ScoreStatus::Reopened->value])
            ->latest('submitted_at')
            ->get();

        return view('chief.verification.index', ['scores' => $scores]);
    }

    public function show(Score $score): View
    {
        $score->load(['run.team.school', 'run.arena', 'run.category.challenges.scoreObjects', 'judge', 'lines.scoreObject', 'approvals.decidedBy']);

        return view('chief.verification.show', ['score' => $score]);
    }

    /**
     * "Tinjau Penilaian" — evidence-first review queue across all active scores.
     */
    public function judging(Request $request): View
    {
        $scores = Score::with(['run.team.school', 'run.arena', 'run.category', 'judge', 'lines.scoreObject', 'run.evidenceFiles'])
            ->whereIn('status', [ScoreStatus::Submitted->value, ScoreStatus::Reopened->value])
            ->latest('submitted_at')
            ->get();

        return view('chief.judging.index', [
            'scores' => $scores,
            'page' => 'judging',
        ]);
    }

    /**
     * "Koreksi Resmi" — list already-official scores that may be corrected,
     * plus the append-only correction history.
     */
    public function correction(Request $request): View
    {
        $score = $request->filled('score')
            ? Score::with(['run.team.school', 'run.arena', 'run.category', 'lines.scoreObject'])
                ->where('uuid', $request->query('score'))
                ->first()
            : Score::with(['run.team.school', 'run.arena', 'run.category', 'lines.scoreObject'])
                ->where('status', ScoreStatus::Approved->value)
                ->latest('approved_at')
                ->first();

        $official = Score::with(['run.team', 'run.arena'])
            ->where('status', ScoreStatus::Approved->value)
            ->orderByDesc('approved_at')
            ->get();

        $history = ResultApproval::with(['score.run.team', 'decidedBy'])
            ->where('decision', 'corrected')
            ->latest('decided_at')
            ->take(20)
            ->get();

        return view('chief.correction.index', [
            'score' => $score,
            'official' => $official,
            'history' => $history,
            'page' => 'correction',
        ]);
    }

    public function correct(Request $request, Score $score): RedirectResponse
    {
        abort_unless($request->user()->hasPermissionTo('score.approve'), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'lines' => ['required', 'array'],
            'lines.*.line_id' => ['required', 'integer'],
            'lines.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $this->corrections->correct($score, $request->user(), $validated['lines'], $validated['reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['correction' => $e->getMessage()]);
        }

        return redirect()
            ->route('chief.correction.index', ['score' => $score->uuid])
            ->with('status', 'Koreksi resmi tersimpan dan total diperbarui.');
    }

    public function approve(Request $request, Score $score): RedirectResponse
    {
        abort_unless($request->user()->can('approve', $score), 403, 'Maker-checker: penilai tidak boleh menyetujui skornya sendiri.');

        $this->publication->approve($score, $request->user());

        return redirect()
            ->route('chief.verification.index')
            ->with('status', 'Skor disetujui.');
    }

    public function reject(Request $request, Score $score): RedirectResponse
    {
        abort_unless($request->user()->can('approve', $score), 403);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $this->publication->reject($score, $request->user(), $validated['reason']);

        return redirect()
            ->route('chief.verification.index')
            ->with('status', 'Skor ditolak dan dikembalikan ke juri.');
    }

    public function reopen(Request $request, Score $score): RedirectResponse
    {
        abort_unless($request->user()->can('reopen', $score), 403);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        try {
            $this->publication->reopen($score, $request->user(), $validated['reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()
            ->route('chief.verification.index')
            ->with('status', 'Skor dibuka ulang.');
    }
}
