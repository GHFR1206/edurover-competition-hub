@extends('layouts.roborace-coach')

@section('title', 'Tim Saya')

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
                <h1>Tim saya</h1>
                <p>Semua tim sekolah Anda beserta statusnya.</p>
            </div>
            <img class="hero-mascot schedule-mascot" src="{{ asset('assets/17d7a.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="content-width page-stack">
        <section class="card reveal">
            <span class="eyebrow">DAFTAR TIM · {{ $teams->count() }} TIM</span>
            <h2>Data tim</h2>
            <div class="data-list">
                @forelse ($teams as $team)
                    <div class="data-row">
                        <div>
                            <strong>{{ $team->name }}</strong>
                            <span>{{ $team->category?->level->publicLabel() }} · {{ $team->school?->name }} · {{ $team->runs->count() }} run</span>
                        </div>
                        <span class="status-badge {{ $team->status->value === 'verified' ? 'success' : 'warning' }}">{{ $team->status->label() }}</span>
                        <a class="button secondary" href="{{ route('coach.teams.show', $team) }}">Detail</a>
                    </div>
                @empty
                    <p>Belum ada tim.</p>
                @endforelse
            </div>
        </section>
    </div>
</main>
@endsection
