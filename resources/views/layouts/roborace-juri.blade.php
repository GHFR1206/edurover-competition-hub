<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace') · Juri</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/juri.css'])
</head>
<body>
@php
    $juriNav = [
        ['id' => 'remote', 'label' => 'Suara Remote', 'route' => 'judge.tasks.index'],
        ['id' => 'manual', 'label' => 'Nilai Manual', 'route' => 'judge.scores.mine'],
        ['id' => 'leaderboard', 'label' => 'Leaderboard', 'route' => 'public.leaderboard'],
    ];
    $activeNav = $page ?? 'remote';
    $user = auth()->user();
@endphp
<div class="app-shell">
    <nav class="navbar">
        <a class="brand" href="{{ route('judge.dashboard') }}">Robo<span>Race</span></a>
        <div class="navlinks">
            @foreach ($juriNav as $item)
                <a class="{{ $activeNav === $item['id'] ? 'active' : '' }}" href="{{ route($item['route']) }}" style="display:inline-flex;align-items:center;text-decoration:none;">{{ $item['label'] }}</a>
            @endforeach
        </div>
        <div class="navtools">
            <span class="connection">● Online</span>
            <span class="account"><i>{{ strtoupper(mb_substr($user->name, 0, 1)) }}</i><span>{{ $user->name }}</span></span>
        </div>
    </nav>

    @yield('content')

    <x-roborace.role-footer variant="juri" />
</div>
@stack('scripts')
</body>
</html>
