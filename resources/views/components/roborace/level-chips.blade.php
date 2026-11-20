{{--
    RoboRace level filter chips (SD / SMP / SMA).
    Props: $active ('sd'|'smp'|'sma'), $levels (collection of Level), $dark,
           $route, $param (query key, default 'level').
--}}
@php
    $levels = $levels ?? collect(\App\Enums\Level::cases());
    $param = $param ?? 'level';
    $activeValue = $active instanceof \App\Enums\Level ? $active->value : $active;
@endphp
<div class="chips {{ ($dark ?? false) ? 'dark-chips' : '' }}" role="group" aria-label="Filter jenjang">
    @foreach ($levels as $level)
        @php($value = $level instanceof \App\Enums\Level ? $level->value : $level)
        @php($label = $level instanceof \App\Enums\Level ? $level->label() : strtoupper($level))
        <a href="{{ route($route ?? 'home', array_merge(request()->query(), [$param => $value])) }}"
           class="{{ $activeValue === $value ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>
