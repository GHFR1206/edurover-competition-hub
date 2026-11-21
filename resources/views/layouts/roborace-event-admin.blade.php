<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace') · Event Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/event-admin.css', 'resources/css/roborace/account.css'])
</head>
<body>
@php
    $adminNav = [
        ['id' => 'events', 'label' => 'Event & Ruleset', 'route' => 'eventadmin.events.index'],
        ['id' => 'staff', 'label' => 'Staf & Penugasan', 'route' => 'eventadmin.staff.index'],
        ['id' => 'schedule', 'label' => 'Jadwal & Arena', 'route' => 'eventadmin.schedule.index'],
        ['id' => 'tokens', 'label' => 'Token Arena', 'route' => 'eventadmin.tokens.index'],
        ['id' => 'reports', 'label' => 'Laporan', 'route' => 'eventadmin.reports.index'],
    ];
    $activeNav = ($page ?? 'events') === 'setup' ? 'events' : ($page ?? 'events');
@endphp
<div class="app page-{{ $page ?? 'events' }}">
    <header class="navbar">
        <a class="brand" href="{{ route('eventadmin.dashboard') }}" aria-label="RoboRace beranda">Robo<span>Race</span></a>
        <nav aria-label="Navigasi admin">
            @foreach ($adminNav as $item)
                <a class="{{ $activeNav === $item['id'] ? 'active' : '' }}" href="{{ route($item['route']) }}" style="display:inline-flex;align-items:center;text-decoration:none;">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="nav-right"><span class="admin-label">Event Admin</span><x-roborace.account-menu /></div>
    </header>

    <section class="hero">
        <div class="live-row">
            <span class="live">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span>@yield('series', 'Seri Pilot')</span>
        </div>
        <div class="hero-content">
            <div>
                <h1>@yield('hero-title')</h1>
                <p>@yield('hero-subtitle')</p>
            </div>
            <img class="mascot" src="{{ asset('assets/' . (trim($__env->yieldContent('hero-mascot')) ?: '17d7a.png')) }}" alt="Maskot RoboRace">
        </div>
    </section>

    <main>
        <div class="decorations" aria-hidden="true">
            <span class="gear">✤</span>
            <span class="flag">▦</span>
            <span class="hex">⬢</span>
            <span class="triangle">▲</span>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <x-roborace.role-footer variant="default" />
</div>
@stack('scripts')
</body>
</html>
