<?php

namespace App\Http\Controllers\Chief;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Chief Judge appeal resolution (banding). The chief reviews open appeals and
 * accepts or rejects them with a mandatory resolution note.
 */
class AppealController extends Controller
{
    public function index(Request $request): View
    {
        $appeals = Appeal::with(['run.arena', 'run.category', 'run.team.school', 'team'])
            ->latest()
            ->get();

        return view('chief.appeals.index', [
            'appeals' => $appeals,
            'page' => 'appeals',
        ]);
    }

    public function resolve(Request $request, Appeal $appeal): RedirectResponse
    {
        abort_unless($request->user()->can('appeal.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:under_review,accepted,rejected'],
            'resolution' => ['required', 'string', 'max:2000'],
        ]);

        $appeal->status = $validated['status'];
        $appeal->resolution = $validated['resolution'];
        $appeal->resolved_by = $request->user()->id;
        $appeal->resolved_at = in_array($validated['status'], ['accepted', 'rejected'], true) ? now() : null;
        $appeal->save();

        return back()->with('status', 'Banding diperbarui.');
    }
}
