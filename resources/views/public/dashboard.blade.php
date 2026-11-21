@extends('layouts.roborace-public')

@section('title', 'RoboRace · Dasbor Publik')

@php
    $statusClass = fn (\App\Enums\RunStatus $s) => match ($s) {
        \App\Enums\RunStatus::Finished => 'selesai',
        \App\Enums\RunStatus::Running => 'sedang-berjalan',
        \App\Enums\RunStatus::Called, \App\Enums\RunStatus::Ready => 'berikutnya',
        \App\Enums\RunStatus::Scheduled, \App\Enums\RunStatus::ReRun => 'terjadwal',
        default => 'sementara',
    };
@endphp

@section('content')
<div class="app lavender-page dash-page">
    <x-roborace.header :page="$page" />

    <section class="dash-hero">
        <div class="dash-copy">
            <span class="eyebrow">DASBOR PUBLIK</span>
            <h1>Halo, {{ auth()->user()->name }}!</h1>
            <p>
                Pantau arena, jadwal, dan papan peringkat dari satu tempat.
                @if ($event)
                    Event aktif: <strong>{{ $event->name }}</strong>.
                @else
                    Belum ada event yang berjalan saat ini.
                @endif
            </p>
            <div class="hero-actions">
                <a class="purple-btn" href="{{ route('public.leaderboard') }}">Lihat leaderboard</a>
                <a class="gold-btn" href="{{ route('public.schedule') }}">Jadwal pertandingan</a>
            </div>
        </div>
        <x-roborace.mascot src="786e4.png" class="dash-mascot" />
    </section>

    <main class="wide-main dash-main">
        <section class="stats-grid">
            <article class="card reveal">
                <small>SEKOLAH TERDAFTAR</small>
                <strong>{{ $schoolCount }}</strong>
                <p>Dari berbagai wilayah</p>
            </article>
            <article class="card reveal" style="animation-delay:100ms">
                <small>TIM BERTANDING</small>
                <strong>{{ $teamCount }}</strong>
                <p>Di {{ $categories->count() }} kategori lomba</p>
            </article>
            <article class="card reveal" style="animation-delay:200ms">
                <small>ARENA AKTIF</small>
                <strong>{{ str_pad($arenaCount, 2, '0', STR_PAD_LEFT) }}</strong>
                <p>{{ $publishedCount }} kategori sudah dipublikasikan</p>
            </article>
        </section>

        <section class="quick-grid">
            <a class="card quick-card reveal" href="{{ route('public.events') }}">
                <span class="quick-icon">◆</span>
                <strong>Event</strong>
                <small>Tiga level, tiga petualangan</small>
            </a>
            <a class="card quick-card reveal" style="animation-delay:80ms" href="{{ route('public.schedule') }}">
                <span class="quick-icon">◷</span>
                <strong>Jadwal</strong>
                <small>Giliran tim dan arena hari ini</small>
            </a>
            <a class="card quick-card reveal" style="animation-delay:160ms" href="{{ route('public.leaderboard') }}">
                <span class="quick-icon">✦</span>
                <strong>Leaderboard</strong>
                <small>Peringkat sementara dan resmi</small>
            </a>
            <a class="card quick-card reveal" style="animation-delay:240ms" href="{{ route('public.results') }}">
                <span class="quick-icon">✓</span>
                <strong>Hasil Resmi</strong>
                <small>Podium yang sudah diverifikasi</small>
            </a>
            <a class="card quick-card reveal" style="animation-delay:320ms" href="{{ route('public.archive') }}">
                <span class="quick-icon">▣</span>
                <strong>Arsip Hasil</strong>
                <small>Jejak juara dari tahun ke tahun</small>
            </a>
            <a class="card quick-card reveal" style="animation-delay:400ms" href="{{ route('profile.edit') }}">
                <span class="quick-icon">☺</span>
                <strong>Profil</strong>
                <small>Perbarui data akun dan sandi</small>
            </a>
        </section>

        <section class="dash-columns">
            <article class="card reveal">
                <small>JADWAL TERDEKAT</small>
                <h2>Arena berikutnya</h2>
                @forelse ($upcomingRuns as $run)
                    <div class="schedule-row">
                        <div>
                            <strong>
                                {{ $run->scheduled_at?->format('H:i') ?? '—' }}
                                · {{ $run->arena?->name ?? 'Arena' }}
                                · {{ $run->team?->name }}
                            </strong>
                            <p>
                                {{ $run->team?->school?->name }}
                                · {{ $run->category?->level->publicLabel() }}
                            </p>
                        </div>
                        <span class="status {{ $statusClass($run->status) }}">{{ $run->status->label() }}</span>
                    </div>
                @empty
                    <p class="muted">Belum ada jadwal pertandingan yang bisa ditampilkan.</p>
                @endforelse
                <a class="outline-btn mini" href="{{ route('public.schedule') }}">Jadwal lengkap →</a>
            </article>

            <article class="card reveal" style="animation-delay:120ms">
                <small>PAPAN PERINGKAT</small>
                <h2>{{ $featured?->level->publicLabel() ?? 'Peringkat' }}</h2>
                <div class="dash-standings">
                    @forelse ($featuredStandings as $row)
                        <a class="dash-rank" href="{{ route('public.leaderboard', $featured ? ['level' => $featured->level->value] : []) }}">
                            <b>{{ $row['rank'] }}</b>
                            <div>
                                <strong>{{ $row['team']->name }}</strong>
                                <small>{{ $row['team']->school?->name }}</small>
                            </div>
                            <em>{{ $row['best_total'] }}</em>
                        </a>
                    @empty
                        <p class="muted">Belum ada skor untuk ditampilkan.</p>
                    @endforelse
                </div>
                <a class="purple-btn mini" href="{{ route('public.leaderboard') }}">Buka leaderboard →</a>
            </article>
        </section>

        @if ($categories->isNotEmpty())
            <article class="card reveal dash-categories">
                <small>KATEGORI LOMBA</small>
                <h2>Pilih arena petualanganmu</h2>
                <div class="level-line-list">
                    @foreach ($categories as $category)
                        <div class="level-line">
                            <div>
                                <strong>{{ $category->level->publicLabel() }} · {{ $category->level->challengeName() }}</strong>
                                <p>{{ $category->status->label() }} · {{ $category->persona ?? '' }}</p>
                            </div>
                            <a class="outline-btn mini" href="{{ route('public.schedule', ['level' => $category->level->value]) }}">Lihat jadwal</a>
                        </div>
                    @endforeach
                </div>
            </article>
        @endif
    </main>

    <x-roborace.footer />
</div>
@endsection
