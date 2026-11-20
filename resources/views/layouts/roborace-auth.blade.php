<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/auth.css'])
</head>
<body>
<div class="app @yield('app-class')">
    <header class="header">
        <a class="brand" href="{{ url('/') }}" aria-label="RoboRace">Robo<span>Race</span></a>
        <nav class="nav" aria-label="Navigasi utama">
            <a href="{{ url('/') }}">Beranda</a>
            <a href="{{ route('public.events') }}">Event</a>
            <a href="{{ route('public.schedule') }}">Jadwal</a>
            <a href="{{ route('public.leaderboard') }}">Leaderboard</a>
            <a href="{{ route('public.results') }}">Hasil Resmi</a>
            <a href="{{ route('public.archive') }}">Arsip Hasil</a>
        </nav>
        <div class="header-actions">
            <a class="header-login" href="{{ route('login') }}">Masuk</a>
            <a class="header-register" href="{{ route('register') }}">Daftar sekolah</a>
        </div>
    </header>

    <main class="main">
        <img class="sky-decoration" src="{{ asset('assets/' . (trim($__env->yieldContent('sky', 'd5b5e.svg')))) }}" alt="">
        <div class="auth-layout @yield('layout-class')">
            <section class="intro">
                @hasSection('intro-eyebrow')
                    <span class="intro-eyebrow">@yield('intro-eyebrow')</span>
                @endif
                <h1>@yield('intro-title')</h1>
                <p>@yield('intro-description')</p>
                <img class="intro-mascot" src="{{ asset('assets/' . (trim($__env->yieldContent('intro-mascot', '17d7a.png')))) }}" alt="">
                @hasSection('intro-note')
                    <div class="mascot-note mascot-note--register">
                        <img src="{{ asset('assets/' . (trim($__env->yieldContent('intro-note-mascot', 'e638f.png')))) }}" alt="">
                        <span>@yield('intro-note')</span>
                    </div>
                @endif
            </section>
            <section class="auth-column">
                @yield('content')
                @hasSection('card-note')
                    <div class="mascot-note">
                        <img src="{{ asset('assets/' . (trim($__env->yieldContent('card-note-mascot', '17d7a.png')))) }}" alt="">
                        <span>@yield('card-note')</span>
                    </div>
                @endif
            </section>
        </div>
    </main>

    <footer class="footer @yield('footer-class')">
        <div class="finish-wave"></div>
        <div class="footer-content">
            <strong>RoboRace</strong>
            <span>Ditenagai Kreativitas. Digerakkan Teknologi.</span>
            <div></div>
            <small>© 2026 RoboRace. All Rights Reserved.</small>
        </div>
        <div class="checkerboard"></div>
    </footer>
</div>
@stack('scripts')
</body>
</html>
