@extends('layouts.roborace-coach')

@section('title', $team->name)

@php($page = 'team')

@section('content')
<section class="hero hero-schedule">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>{{ $team->name }}</h1>
                <p>{{ $team->school?->name }} · {{ $team->category?->level->publicLabel() }}</p>
            </div>
            <img class="hero-mascot schedule-mascot" src="{{ asset('assets/17d7a.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="ornament" aria-hidden="true">
        <img src="{{ asset('assets/98d5a.svg') }}" alt="">
        <span class="gear-cut"><img src="{{ asset('assets/98d5a.svg') }}" alt=""></span>
    </div>
    <div class="content-width page-stack">
        <div class="two-column">
            <section class="card reveal">
                <span class="eyebrow">{{ strtoupper($team->school?->name ?? 'SEKOLAH') }} · {{ $team->category?->level->publicLabel() }}</span>
                <h2>{{ $team->name }}</h2>
                <span class="status-badge {{ $team->status->value === 'verified' ? 'success' : 'warning' }}">{{ $team->status->label() }}</span>
                <p class="body-dark">{{ $team->category?->event?->name ?? 'RoboRace' }} · {{ $team->short_name ?? '—' }}</p>
            </section>
            <section class="card reveal" style="animation-delay:80ms">
                <span class="eyebrow neutral">RINGKASAN RUN</span>
                <h2>{{ $team->runs->count() }} run</h2>
                <p>Total run tercatat untuk tim ini di seluruh arena.</p>
            </section>
        </div>

        <section class="card reveal" style="animation-delay:140ms">
            <h2>Anggota tim</h2>
            <div class="data-list">
                @forelse ($team->participants as $participant)
                    <div class="data-row">
                        <div>
                            <strong>{{ $participant->name }}</strong>
                            <span>{{ $participant->role === 'leader' ? 'Ketua tim' : 'Anggota' }}</span>
                        </div>
                        <span class="status-badge success">Lengkap</span>
                    </div>
                @empty
                    <p>Belum ada anggota tercatat.</p>
                @endforelse
            </div>
            <p class="caption">NISN hanya terlihat oleh panitia, tidak untuk publik.</p>
        </section>

        <section class="card reveal" style="animation-delay:200ms">
            <span class="eyebrow">RIWAYAT · ARENA &amp; SKOR</span>
            <h2>Riwayat run</h2>
            <div class="data-list schedule-list">
                @forelse ($team->runs as $run)
                    <div class="data-row">
                        <div>
                            <strong>{{ $run->arena?->name }}</strong>
                            <span>
                                {{ $run->status->label() }}
                                @foreach ($run->scores as $score)
                                    · {{ $score->status->label() }}: {{ $score->total }}
                                @endforeach
                            </span>
                        </div>
                        <span class="status-badge purple">Run</span>
                    </div>
                @empty
                    <p>Belum ada run untuk tim ini.</p>
                @endforelse
            </div>
            <div class="actions">
                <a class="button secondary" href="{{ route('coach.teams.index') }}">← Kembali</a>
                <a class="button primary" href="{{ route('coach.results.show', $team) }}">Lihat hasil</a>
            </div>
        </section>
    </div>
</main>
@endsection
