<?php

namespace App\Http\Controllers\Api;

use App\Enums\RunStatus;
use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Run;
use App\Services\TimingException;
use App\Services\TimingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Timing API for arena devices.
 *
 * The arena is resolved from the bearer token (never from the request body),
 * so a compromised device can only ever touch its own arena.
 */
class ArenaTimingController extends Controller
{
    public function __construct(private readonly TimingService $timing) {}

    /** The run the arena should be timing right now (or next up). */
    public function currentRun(Request $request): JsonResponse
    {
        $arena = $this->arena($request);

        $run = Run::with(['team', 'category', 'laps'])
            ->where('arena_id', $arena->id)
            ->whereIn('status', [
                RunStatus::Running->value,
                RunStatus::Called->value,
                RunStatus::Ready->value,
                RunStatus::Scheduled->value,
            ])
            ->orderByRaw(
                'case when status in (?, ?, ?) then 0 else 1 end',
                [RunStatus::Running->value, RunStatus::Called->value, RunStatus::Ready->value],
            )
            ->orderBy('scheduled_at')
            ->first();

        return response()->json([
            'arena' => ['id' => $arena->uuid, 'name' => $arena->name, 'code' => $arena->code],
            'run' => $run ? $this->runPayload($run) : null,
        ]);
    }

    /** Start signal. `started_ms` is optional and defaults to 0. */
    public function start(Request $request, Run $run): JsonResponse
    {
        $validated = $request->validate([
            'started_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        return $this->respond($request, $run, [
            'started_ms' => $validated['started_ms'] ?? 0,
        ]);
    }

    /** Lap splits, with or without an explicit start. */
    public function laps(Request $request, Run $run): JsonResponse
    {
        $validated = $request->validate([
            'started_ms' => ['nullable', 'integer', 'min:0'],
            'laps' => ['required', 'array', 'min:1', 'max:200'],
            'laps.*.lap_no' => ['nullable', 'integer', 'min:1', 'max:200'],
            'laps.*.lap_ms' => ['required', 'integer', 'min:1'],
        ]);

        return $this->respond($request, $run, $validated);
    }

    /** Finish signal; `finished_ms` is the total elapsed time. */
    public function finish(Request $request, Run $run): JsonResponse
    {
        $validated = $request->validate([
            'started_ms' => ['nullable', 'integer', 'min:0'],
            'finished_ms' => ['required', 'integer', 'min:1'],
            'laps' => ['nullable', 'array', 'max:200'],
            'laps.*.lap_no' => ['nullable', 'integer', 'min:1', 'max:200'],
            'laps.*.lap_ms' => ['required_with:laps', 'integer', 'min:1'],
        ]);

        return $this->respond($request, $run, $validated);
    }

    /** One-shot payload: start, finish and laps together. */
    public function timing(Request $request, Run $run): JsonResponse
    {
        $validated = $request->validate([
            'started_ms' => ['nullable', 'integer', 'min:0'],
            'finished_ms' => ['nullable', 'integer', 'min:1'],
            'laps' => ['nullable', 'array', 'max:200'],
            'laps.*.lap_no' => ['nullable', 'integer', 'min:1', 'max:200'],
            'laps.*.lap_ms' => ['required_with:laps', 'integer', 'min:1'],
        ]);

        return $this->respond($request, $run, $validated);
    }

    /** Stored timing for one run. */
    public function show(Request $request, Run $run): JsonResponse
    {
        $arena = $this->arena($request);

        abort_unless($run->arena_id === $arena->id, 404, 'Run tidak ditemukan di arena ini.');

        return response()->json(['run' => $this->runPayload($run->load('laps'))]);
    }

    /** @param  array<string, mixed>  $payload */
    private function respond(Request $request, Run $run, array $payload): JsonResponse
    {
        $arena = $this->arena($request);

        // A device must never learn about runs outside its own arena, but the
        // 404 still needs a readable message for the device log.
        abort_unless($run->arena_id === $arena->id, 404, 'Run tidak ditemukan di arena ini.');

        try {
            $result = $this->timing->record($run, $arena, $payload, $request->attributes->get('device_id'));
        } catch (TimingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'accepted' => true,
            'first_seen' => $result['first_seen'],
            'laps_stored' => $result['laps'],
            'run' => $this->runPayload($result['run']),
        ]);
    }

    private function arena(Request $request): Arena
    {
        /** @var Arena $arena */
        $arena = $request->attributes->get('arena');

        return $arena;
    }

    /** @return array<string, mixed> */
    private function runPayload(Run $run): array
    {
        return [
            'id' => $run->uuid,
            'status' => $run->status->value,
            'team' => $run->team?->name,
            'category' => $run->category?->level->publicLabel(),
            'attempt_no' => $run->attempt_no,
            'started_ms' => $run->started_ms,
            'finished_ms' => $run->finished_ms,
            'official_time_ms' => $run->official_time_ms,
            'lap_count' => $run->lap_count,
            'laps' => $run->laps->map(fn ($lap) => [
                'lap_no' => $lap->lap_no,
                'lap_ms' => $lap->lap_ms,
                'cumulative_ms' => $lap->cumulative_ms,
            ])->values(),
        ];
    }
}
