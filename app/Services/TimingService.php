<?php

namespace App\Services;

use App\Enums\RunStatus;
use App\Events\RunStatusChanged;
use App\Models\Arena;
use App\Models\Run;
use App\Models\RunLap;
use App\Models\TelemetryEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ingests official timing from an arena device.
 *
 * A run receives a start time, a finish time and optional lap splits. The
 * aggregate is written to the run (`started_ms`, `finished_ms`,
 * `official_time_ms`) so standings never have to re-read every lap.
 *
 * Design notes:
 * - All times are milliseconds since the run started (monotonic), so clock
 *   skew between the device and the server cannot corrupt a result.
 * - Re-sending the same payload is safe: laps are upserted by `lap_no`, and a
 *   start/finish that is already recorded is left untouched (the first
 *   reading wins). `first_seen` is reported back to the device.
 * - Timing is recorded even when the telemetry feature flag is off; the flag
 *   only controls the *supporting* telemetry stream, not official timing.
 */
class TimingService
{
    /**
     * @param  array{started_ms?:int|null, finished_ms?:int|null, laps?:array<int, array{lap_no?:int, lap_ms:int}>}  $payload
     * @return array{run:Run, laps:int, first_seen:bool}
     */
    public function record(Run $run, Arena $arena, array $payload, ?string $deviceId = null): array
    {
        if ($run->arena_id !== $arena->id) {
            throw new TimingException('Run ini bukan milik arena Anda.');
        }

        if (in_array($run->status, [RunStatus::Dq, RunStatus::NoShow], true)) {
            throw new TimingException("Run berstatus {$run->status->label()} tidak menerima data waktu.");
        }

        $laps = $payload['laps'] ?? [];
        $startedMs = $payload['started_ms'] ?? null;
        $finishedMs = $payload['finished_ms'] ?? null;

        // Compare against what is already stored too, so a finish that
        // arrives after an earlier start is still validated.
        $this->assertConsistent(
            $startedMs ?? $run->started_ms,
            $finishedMs,
            $laps,
        );

        return DB::transaction(function () use ($run, $arena, $startedMs, $finishedMs, $laps, $deviceId) {
            $firstSeen = $run->timing_recorded_at === null;

            // First reading wins: a device that reconnects and replays its
            // buffer must not overwrite an official result.
            if ($startedMs !== null && $run->started_ms === null) {
                $run->started_ms = $startedMs;
                $run->started_at ??= now();
                $run->called_at ??= now();
            }

            if ($finishedMs !== null && $run->finished_ms === null) {
                $run->finished_ms = $finishedMs;
                $run->finished_at ??= now();
            }

            $stored = $this->storeLaps($run, $laps, $deviceId);

            // Official time: explicit finish, else the last lap cumulative.
            $official = $run->finished_ms ?? $run->laps()->max('cumulative_ms');

            if ($official !== null) {
                $run->official_time_ms = (int) $official;
                $run->duration_seconds = intdiv((int) $official, 1000);
            }

            $run->lap_count = $run->laps()->count() ?: $run->lap_count;
            $run->timing_recorded_at = now();
            $run->timing_source = 'api';

            // A finished payload settles the run so it can be scored/published.
            // A DNF/DQ/no-show decision made by the operator is never
            // overwritten by timing data.
            if ($run->finished_ms !== null && ! in_array($run->status, [RunStatus::Dnf, RunStatus::Dq, RunStatus::NoShow], true)) {
                $run->status = RunStatus::Finished;
            } elseif ($run->started_ms !== null && in_array($run->status, [RunStatus::Scheduled, RunStatus::Called, RunStatus::Ready], true)) {
                $run->status = RunStatus::Running;
            }

            $run->save();

            $run->writeAudit('updated', null, [
                'official_time_ms' => $run->official_time_ms,
                'lap_count' => $run->lap_count,
            ], 'Waktu resmi diterima dari perangkat arena');

            $this->logTelemetry($run, $arena, $startedMs, $finishedMs, $laps, $deviceId);

            $fresh = $run->fresh(['laps', 'team', 'arena']);

            // Live boards reload on these events, exactly like operator
            // status changes.
            RunStatusChanged::dispatch($fresh);

            return ['run' => $fresh, 'laps' => $stored, 'first_seen' => $firstSeen];
        });
    }

    /** Record only the start signal (device sends start, then laps, then finish). */
    public function start(Run $run, Arena $arena, int $startedMs, ?string $deviceId = null): Run
    {
        return $this->record($run, $arena, ['started_ms' => $startedMs], $deviceId)['run'];
    }

    /** Record a finish signal without lap detail. */
    public function finish(Run $run, Arena $arena, int $finishedMs, ?string $deviceId = null): Run
    {
        return $this->record($run, $arena, ['finished_ms' => $finishedMs], $deviceId)['run'];
    }

    /**
     * @param  array<int, array{lap_no?:int, lap_ms:int}>  $laps
     */
    private function storeLaps(Run $run, array $laps, ?string $deviceId): int
    {
        if ($laps === []) {
            return 0;
        }

        // Auto-number laps that arrive without an explicit number.
        $next = (int) $run->laps()->max('lap_no');
        $cumulative = (int) $run->laps()->max('cumulative_ms');
        $count = 0;

        foreach ($laps as $lap) {
            $lapNo = $lap['lap_no'] ?? ++$next;
            $lapMs = (int) $lap['lap_ms'];

            $existing = $run->laps()->where('lap_no', $lapNo)->first();

            if ($existing) {
                // First reading wins: a device replaying its buffer after a
                // reconnect must not rewrite an official split.
                $count++;

                continue;
            }

            $cumulative += $lapMs;

            RunLap::create([
                'uuid' => (string) Str::uuid(),
                'run_id' => $run->id,
                'lap_no' => $lapNo,
                'lap_ms' => $lapMs,
                'cumulative_ms' => $cumulative,
                'recorded_at' => now(),
                'source' => 'api',
                'device_id' => $deviceId,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array{lap_no?:int, lap_ms:int}>  $laps
     */
    private function assertConsistent(?int $startedMs, ?int $finishedMs, array $laps): void
    {
        foreach ([$startedMs, $finishedMs] as $value) {
            if ($value !== null && $value < 0) {
                throw new TimingException('Waktu tidak boleh negatif.');
            }
        }

        if ($startedMs !== null && $finishedMs !== null && $finishedMs <= $startedMs) {
            throw new TimingException('Waktu finish harus lebih besar dari waktu start.');
        }

        foreach ($laps as $lap) {
            if (($lap['lap_ms'] ?? 0) <= 0) {
                throw new TimingException('Durasi lap harus lebih besar dari 0.');
            }
        }
    }

    /**
     * Mirror the raw payload into the supporting telemetry stream. This never
     * contributes to scores and is best-effort: a failure must not lose the
     * official timing that was already committed.
     */
    private function logTelemetry(
        Run $run,
        Arena $arena,
        ?int $startedMs,
        ?int $finishedMs,
        array $laps,
        ?string $deviceId,
    ): void {
        try {
            TelemetryEvent::create([
                'uuid' => (string) Str::uuid(),
                'run_id' => $run->id,
                'device_id' => $deviceId,
                'event_type' => 'timing.record',
                'payload' => [
                    'arena' => $arena->code ?? $arena->name,
                    'started_ms' => $startedMs,
                    'finished_ms' => $finishedMs,
                    'laps' => $laps,
                ],
                'occurred_at' => now(),
                'received_at' => now(),
            ]);
        } catch (\Throwable) {
            // Supporting data only.
        }
    }
}
