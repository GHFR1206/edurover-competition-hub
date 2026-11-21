@extends('layouts.roborace-public')

@section('title', 'RoboRace · Beranda')

@section('content')
<div class="app home-page">
    <x-roborace.header :page="$page" />

    <section class="home-hero">
        <img class="cloud cloud-a" src="{{ asset('assets/53c8e.svg') }}" alt="">
        <img class="cloud cloud-b" src="{{ asset('assets/53c8e.svg') }}" alt="">
        <div class="home-copy">
            <x-roborace.live-badge />
            <h1>
                Lintasan menyala,
                <br>
                <em>robotmu siap</em>
                <br>
                <em>melesat.</em>
            </h1>
            <p>
                Dari ruang kelas ke garis finis. Rakit ide, uji keberanian, dan rayakan setiap
                pencapaian robot sekolahmu.
            </p>
            <div class="hero-actions">
                <a class="purple-btn" href="{{ route('public.leaderboard') }}">Lihat leaderboard</a>
                <a class="gold-btn" href="{{ route('public.events') }}">Daftar sekolah</a>
            </div>
        </div>
        <x-roborace.mascot src="74589.png" class="home-mascot" />
    </section>

    <main>
        <section class="stats-grid">
            <article class="card reveal">
                <small>SEKOLAH TERDAFTAR</small>
                <strong>{{ $schoolCount }}</strong>
                <p>Dari berbagai wilayah</p>
            </article>
            <article class="card reveal" style="animation-delay:100ms">
                <small>TIM SIAP BERTANDING</small>
                <strong>{{ $teamCount }}</strong>
                <p>Semua kategori lomba</p>
            </article>
            <article class="card reveal" style="animation-delay:200ms">
                <small>EVENT BERJALAN</small>
                <strong>{{ $event ? '01' : '00' }}</strong>
                <p>{{ $event?->name ?? 'Belum ada event aktif' }}</p>
            </article>
        </section>

        <section class="section-block">
            <h2>Pilih arena petualanganmu</h2>
            <p class="sub">Setiap tim bebas memilih dari tiga level yang seru.</p>
            <div class="event-grid">
                @forelse ($categories as $category)
                    <article class="card reveal">
                        <small>{{ strtoupper($category->level->publicLabel()) }} · {{ strtoupper($category->persona ?? '') }}</small>
                        <h3>{{ $category->level->challengeName() }}</h3>
                        <span class="yellow-pill">{{ $category->status->label() }}</span>
                        <p>{{ $category->description ?? $category->name }}</p>
                        <p class="muted">Tim: {{ $teamCount }} · Arena: {{ $arenaCount }}</p>
                        <a class="purple-btn" href="{{ route('public.schedule', ['level' => $category->level->value]) }}">Lihat jadwal</a>
                    </article>
                @empty
                    <article class="card reveal">
                        <h3>Belum ada kategori</h3>
                        <p class="muted">Event akan ditampilkan setelah panitia mempublikasikannya.</p>
                    </article>
                @endforelse
            </div>
        </section>
    </main>

    <section class="home-leader">
        <div class="section-inner">
            <span class="eyebrow">✦ PAPAN PERINGKAT</span>
            <h2>Leaderboard</h2>
            @forelse ($featuredStandings as $row)
                <a class="rank-row" href="{{ route('public.leaderboard', ['level' => $featured?->level->value]) }}">
                    <b>{{ $row['rank'] }}</b>
                    <div>
                        <strong>{{ $row['team']->name }}</strong>
                        <small>{{ $row['team']->school?->name }} · {{ strtoupper($featured?->level->publicLabel() ?? '') }}</small>
                    </div>
                    <span class="status {{ ($featured?->isPublished() ?? false) ? 'resmi' : 'sementara' }}">
                        {{ ($featured?->isPublished() ?? false) ? '✓ Resmi' : '◷ Sementara' }}
                    </span>
                    <em><x-roborace.result-value :row="$row" :unit="false" /></em>
                </a>
            @empty
                <p class="muted" style="color:#c9b8f0">Belum ada skor resmi untuk ditampilkan.</p>
            @endforelse
            <a class="gold-btn mini" href="{{ route('public.leaderboard') }}">Buka leaderboard lengkap</a>
        </div>
    </section>

    <section class="mini-results">
        <x-roborace.live-badge />
        <h2>Hasil Resmi</h2>
        <p>{{ $event?->name ?? 'Kompetisi Robotika EduRover' }}</p>
        <x-roborace.mascot src="786e4.png" />
        <div class="mini-podium">
            <div>
                <b>2</b>
                <strong>{{ $featuredStandings[1]['team']->name ?? '—' }}</strong>
                <small>@isset($featuredStandings[1])<x-roborace.result-value :row="$featuredStandings[1]" />@else Menunggu @endisset</small>
            </div>
            <a class="podium-first" href="{{ route('public.results') }}" style="text-decoration:none;color:inherit">
                <span>JUARA 1</span>
                <b>1</b>
                <strong>{{ $featuredStandings[0]['team']->name ?? '—' }}</strong>
                <small>@isset($featuredStandings[0])<x-roborace.result-value :row="$featuredStandings[0]" />@else Menunggu @endisset</small>
            </a>
            <div>
                <b>3</b>
                <strong>{{ $featuredStandings[2]['team']->name ?? 'Belum ditetapkan' }}</strong>
                <small>@isset($featuredStandings[2])<x-roborace.result-value :row="$featuredStandings[2]" />@endisset</small>
            </div>
        </div>
        <a class="outline-btn" href="{{ route('public.results') }}">Lihat hasil resmi →</a>
    </section>

    <section class="home-cta">
        <div>
            <small>SIAP IKUT?</small>
            <h2>Tertarik Ikut Bertanding?</h2>
            <p>Jangan lewatkan kesempatan untuk belajar dan berkompetisi.</p>
            <a class="gold-btn" href="{{ route('register') }}">Daftarkan timmu</a>
        </div>
        <x-roborace.mascot src="e638f.png" />
    </section>

    <x-roborace.footer />
</div>
@endsection
