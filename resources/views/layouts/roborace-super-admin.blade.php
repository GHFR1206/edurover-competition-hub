<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RoboRace') · Super Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&family=Inter:wght@400;700&display=swap">
    @vite(['resources/css/roborace/super-admin.css'])
</head>
<body>
@php
    $superNav = [
        ['id' => 'ringkasan', 'label' => 'Ringkasan', 'route' => 'superadmin.dashboard'],
        ['id' => 'verifikasi', 'label' => 'Verifikasi Tim', 'route' => 'superadmin.verification.index'],
        ['id' => 'pengguna', 'label' => 'Pengguna', 'route' => 'superadmin.users.index'],
        ['id' => 'audit', 'label' => 'Audit Log', 'route' => 'superadmin.audit.index'],
        ['id' => 'pengaturan', 'label' => 'Pengaturan', 'route' => 'superadmin.settings.index'],
    ];
    $activeNav = $page ?? 'ringkasan';
    $superPageCopy = [
        'ringkasan' => ['Halo, Super Admin!', 'Semua arena dalam pantauan. Mari jaga kompetisi tetap seru, aman, dan adil.'],
        'verifikasi' => ['Bantu tim menuju arena.', 'Periksa data sekolah, anggota, dan catatan sebelum memberi akses robot.'],
        'pengguna' => ['Orang hebat di balik robot.', 'Kelola akses dengan peran yang tepat untuk setiap anggota komunitas.'],
        'audit' => ['Setiap aksi punya jejak.', 'Pelaku, objek, dan perubahan tersimpan untuk menjaga transparansi kompetisi.'],
        'pengaturan' => ['Aturan sistem, tetap terarah.', 'Atur keamanan akun dan metode penilaian default RoboRace.'],
    ];
    [$superTitle, $superSubtitle] = $superPageCopy[$activeNav] ?? $superPageCopy['ringkasan'];
    $mascotByPage = ['verifikasi' => 'd8b40.png', 'pengguna' => 'dc500.png'];
@endphp
<div class="app page-{{ $activeNav }}">
    <nav class="navbar">
        <a class="brand" href="{{ route('superadmin.dashboard') }}"><span>Robo</span><b>Race</b></a>
        <div class="navlinks">
            @foreach ($superNav as $item)
                <a class="{{ $activeNav === $item['id'] ? 'active' : '' }}" href="{{ route($item['route']) }}" style="display:inline-flex;align-items:center;text-decoration:none;">{{ $item['label'] }}</a>
            @endforeach
        </div>
        <span class="admin-label">Super Admin</span>
    </nav>

    <main class="page {{ ($modalOpen ?? false) ? 'modal-open' : '' }}">
        <header class="page-hero {{ $activeNav === 'ringkasan' ? 'summary-hero' : '' }}">
            <div class="live-row">
                <span class="live-pill">{{ $activeNav === 'ringkasan' ? '✦ PUSAT KENDALI ROBO RACE' : '● LANGSUNG · ' . strtoupper(now()->translatedFormat('d M Y')) }}</span>
                @if ($activeNav !== 'ringkasan')<span class="series">Seri Pilot</span>@endif
            </div>
            <div class="hero-copy">
                <div>
                    <h1>{{ $superTitle }}</h1>
                    <p>{{ $superSubtitle }}</p>
                    @hasSection('hero-actions')<div class="hero-actions">@yield('hero-actions')</div>@endif
                    @hasSection('hero-note')<small>@yield('hero-note')</small>@endif
                </div>
                <img class="mascot" src="{{ asset('assets/' . ($mascotByPage[$activeNav] ?? '17d7a.png')) }}" alt="">
            </div>
        </header>

        @if (session('status'))
            <div class="content"><div class="notice">{{ session('status') }}</div></div>
        @endif
        @if ($errors->any())
            <div class="content">
                <div class="notice" style="background:#fde3e7;color:#b3283f">
                    <ul>
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <x-roborace.role-footer variant="default" />
</div>
@stack('scripts')
</body>
</html>
