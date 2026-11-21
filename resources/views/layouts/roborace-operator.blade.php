<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace') · Operator Arena</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @include('partials.realtime-meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/operator.css', 'resources/js/realtime.js', 'resources/css/roborace/account.css'])
</head>
<body>
@php($user = auth()->user())
<div class="app-shell">
    <nav class="navbar" aria-label="Navigasi utama">
        <a class="brand" href="{{ route('operator.dashboard') }}" aria-label="RoboRace">Robo<span>Race</span></a>
        <div class="nav-menu">
            <a class="nav-active" href="{{ route('operator.arenas.index') }}">Antrean Arena</a>
            <a href="{{ route('operator.incidents.index') }}">Insiden</a>
        </div>
        <div class="nav-right"><span class="operator-label">Operator Arena</span><x-roborace.account-menu /></div>
    </nav>

    @yield('content')

    <x-roborace.role-footer variant="operator" />
</div>
@stack('scripts')
</body>
</html>
