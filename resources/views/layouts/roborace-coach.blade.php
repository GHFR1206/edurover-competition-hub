<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace') · Coach</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/coach.css', 'resources/css/roborace/account.css'])
</head>
<body>
@php
    $coachNav = [
        ['id' => 'team', 'label' => 'Tim saya', 'route' => 'coach.teams.index'],
        ['id' => 'results', 'label' => 'Hasil', 'route' => 'coach.results.index'],
        ['id' => 'appeals', 'label' => 'Banding', 'route' => 'coach.appeals.index'],
        ['id' => 'schedule', 'label' => 'Jadwal', 'route' => 'public.schedule'],
    ];
    $activeNav = $page ?? 'team';
@endphp
<div class="app-shell">
    <header class="navbar">
        <a class="brand" href="{{ route('coach.dashboard') }}" aria-label="RoboRace, buka Tim saya">
            <span>Robo</span><span>Race</span>
        </a>
        <nav aria-label="Menu coach">
            @foreach ($coachNav as $item)
                <a class="nav-item {{ $activeNav === $item['id'] ? 'active' : '' }}" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="nav-right"><span class="coach-chip">Coach</span><x-roborace.account-menu /></div>
    </header>

    <x-roborace.flash />

    @yield('content')

    <x-roborace.role-footer variant="wave" />
</div>
@stack('scripts')
</body>
</html>
