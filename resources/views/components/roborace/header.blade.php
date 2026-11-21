{{--
    RoboRace public header.
    Ported from frontend/roborace/apps/publik/src/components.tsx (Header).
--}}
@php
    $rrNav = [
        ['id' => 'beranda', 'label' => 'Beranda', 'route' => 'home'],
        ['id' => 'event', 'label' => 'Event', 'route' => 'public.events'],
        ['id' => 'jadwal', 'label' => 'Jadwal', 'route' => 'public.schedule'],
        ['id' => 'leaderboard', 'label' => 'Leaderboard', 'route' => 'public.leaderboard'],
        ['id' => 'hasil', 'label' => 'Hasil Resmi', 'route' => 'public.results'],
        ['id' => 'arsip', 'label' => 'Arsip Hasil', 'route' => 'public.archive'],
    ];
@endphp

<header class="site-header {{ ($dark ?? false) ? 'dark-header' : '' }}">
    <a class="logo" href="{{ route('home') }}"><span>Robo</span><b>Race</b></a>
    <nav aria-label="Navigasi utama">
        @foreach ($rrNav as $item)
            <a href="{{ route($item['route']) }}"
               class="{{ ($page ?? 'beranda') === $item['id'] ? 'active' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
    <div class="account-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="outline-btn account-dashboard">Dasbor</a>
            <a href="{{ route('profile.edit') }}" class="outline-btn account-profile">Profil</a>
            <form method="POST" action="{{ route('logout') }}" class="account-logout">
                @csrf
                <button type="submit" class="gold-btn">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="outline-btn">Masuk</a>
            <a href="{{ route('register') }}" class="gold-btn">Daftar sekolah</a>
        @endauth
    </div>
</header>
