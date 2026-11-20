<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\IncidentReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Arena incident reporting. Operators log technical / robot / network issues
 * on the arenas they are assigned to, and update their status.
 */
class IncidentController extends Controller
{
    private const SEVERITIES = ['info', 'warning', 'critical'];

    private const CATEGORIES = ['technical', 'robot', 'network', 'other'];

    public function index(Request $request): View
    {
        $arenaIds = $this->arenaIds($request);

        $incidents = IncidentReport::with(['arena', 'run.team'])
            ->whereIn('arena_id', $arenaIds)
            ->latest()
            ->get();

        return view('operator.incidents.index', [
            'incidents' => $incidents,
            'arenas' => Arena::whereIn('id', $arenaIds)->orderBy('name')->get(),
            'severities' => self::SEVERITIES,
            'categories' => self::CATEGORIES,
            'activeStatus' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'arena_id' => ['required', 'exists:arenas,id'],
            'run_id' => ['nullable', 'exists:runs,id'],
            'severity' => ['required', 'in:info,warning,critical'],
            'category' => ['nullable', 'in:technical,robot,network,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless(in_array((int) $validated['arena_id'], $this->arenaIds($request)->all(), true), 403);

        $arena = Arena::findOrFail($validated['arena_id']);

        IncidentReport::create([
            'uuid' => (string) Str::uuid(),
            'event_id' => $arena->event_id,
            'arena_id' => $arena->id,
            'run_id' => $validated['run_id'] ?? null,
            'reported_by' => $request->user()->id,
            'severity' => $validated['severity'],
            'category' => $validated['category'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('status', "Insiden \"{$validated['title']}\" dilaporkan.");
    }

    public function update(Request $request, IncidentReport $incident): RedirectResponse
    {
        abort_unless(in_array((int) $incident->arena_id, $this->arenaIds($request)->all(), true), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:open,investigating,resolved'],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ]);

        $incident->status = $validated['status'];
        $incident->resolution = $validated['resolution'] ?? $incident->resolution;
        $incident->resolved_at = $validated['status'] === 'resolved' ? now() : null;
        $incident->save();

        return back()->with('status', "Insiden \"{$incident->title}\" diperbarui.");
    }

    /** @return Collection<int, int> */
    private function arenaIds(Request $request): Collection
    {
        return $request->user()->arenaAssignments()
            ->where('role', 'operator')
            ->where('is_active', true)
            ->pluck('arena_id');
    }
}
