<?php

namespace App\Http\Controllers\Chief;

use App\Enums\ScoreStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Score;
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
    public function __construct(private readonly PublicationService $publication) {}

    public function dashboard(Request $request): View
    {
        return view('chief.dashboard', [
            'submitted' => Score::where('status', ScoreStatus::Submitted->value)->count(),
            'reopened' => Score::where('status', ScoreStatus::Reopened->value)->count(),
            'approved' => Score::where('status', ScoreStatus::Approved->value)->count(),
            'categories' => Category::with('event')->get(),
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
