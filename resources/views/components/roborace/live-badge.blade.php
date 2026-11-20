{{--
    RoboRace live/event badge row.
    Props: $date (display date string, default event date), $series.
--}}
<div class="badge-row">
    <span class="live">● LANGSUNG · {{ $date ?? '14 NOV 2026' }}</span>
    <span class="series">{{ $series ?? 'Seri Pilot' }}</span>
</div>
