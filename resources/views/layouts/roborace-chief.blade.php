<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace') · Chief Judge</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/chief.css'])
</head>
<body>
@php
    // "Review & Publikasi" and "Tinjau Penilaian" both share the verification area.
    $chiefNav = [
        ['id' => 'review', 'label' => 'Review & Publikasi', 'route' => 'chief.verification.index'],
        ['id' => 'judging', 'label' => 'Tinjau Penilaian', 'route' => 'chief.verification.index'],
        ['id' => 'correction', 'label' => 'Koreksi Resmi', 'route' => 'chief.verification.index'],
        ['id' => 'results', 'label' => 'Hasil Resmi', 'route' => 'public.results'],
        ['id' => 'leaderboard', 'label' => 'Leaderboard', 'route' => 'public.leaderboard'],
    ];
    $activeNav = $page ?? 'review';
@endphp
<main>
    <nav class="navbar">
        <a class="brand" href="{{ route('chief.dashboard') }}" aria-label="RoboRace">Robo<span>Race</span></a>
        <div class="navlinks">
            @foreach ($chiefNav as $item)
                <a class="{{ $activeNav === $item['id'] ? 'active' : '' }}" href="{{ route($item['route']) }}" style="display:inline-flex;align-items:center;text-decoration:none;">{{ $item['label'] }}</a>
            @endforeach
        </div>
        <span class="role-chip">Chief Judge</span>
    </nav>

    @hasSection('hero')
        <header class="hero">
            <div class="event-row">
                <span class="live">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
                <span>@yield('series', 'Seri Pilot')</span>
            </div>
            <div class="hero-row">
                <div>
                    <h1>@yield('hero-title')</h1>
                    <p>@yield('hero-subtitle')</p>
                </div>
                <img class="hero-mascot" src="{{ asset('assets/' . (trim($__env->yieldContent('hero-mascot')) ?: '786e4.png')) }}" alt="Maskot RoboRace">
            </div>
        </header>
    @endif

    <section class="content">
        <img class="track-decoration" src="{{ asset('assets/98d5a.svg') }}" alt="">
        <div class="gear">✿</div>
        <div class="hexagon"></div>
        <div class="triangle"></div>
        <div class="flag"></div>
        @yield('content')
    </section>

    <x-roborace.role-footer variant="default" />
</main>
@stack('scripts')
</body>
</html>
