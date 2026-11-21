@props(['row', 'unit' => true])

{{--
    Renders the official value of a standings row.

    A category is scored either by judges (`jury`) or by arena timing (`time`).
    Public pages must never print "0 poin" for a time-based category, so every
    table uses this component instead of reading `best_total` directly.
--}}
@php
    $mode = $row['mode'] ?? 'jury';
    $timeMs = $row['time_ms'] ?? null;
@endphp
@if ($mode === 'time')
    {{ $timeMs !== null ? \App\Services\StandingsService::formatMs($timeMs) : '—' }}@if ($unit && $timeMs !== null) <small>WAKTU</small>@endif
@else
    {{ $row['best_total'] ?? 0 }}@if ($unit) poin @endif
@endif
