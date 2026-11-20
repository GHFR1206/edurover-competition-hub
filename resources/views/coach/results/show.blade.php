@extends('layouts.roborace-coach')

@section('title', 'Hasil ' . $team->name)

@php($page = 'results')

@section('content')
<section class="hero hero-schedule">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>Hasil {{ $team->name }}</h1>
                <p>{{ $team->school?->name }} · {{ $team->category?->level->publicLabel() }}</p>
            </div>
            <img class="hero-mascot schedule-mascot" src="{{ asset('assets/786e4.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="content-width page-stack">
        @forelse ($runs as $run)
            <section class="card reveal">
                <span class="eyebrow">{{ strtoupper($run->arena?->name ?? 'ARENA') }}</span>
                <h2>{{ $run->arena?->name ?? 'Run' }}</h2>
                <span class="status-badge purple">{{ $run->status->label() }}</span>
                @foreach ($run->scores as $score)
                    <div class="score-row">
                        <div>
                            <strong>{{ $score->status->label() }}</strong>
                            <span>Total {{ $score->total }} (subtotal {{ $score->subtotal }} − penalti {{ $score->penalty_total }})</span>
                        </div>
                        <div>
                            <b>{{ $score->total }}</b>
                            <span class="status-badge {{ $score->status->value === 'approved' ? 'success' : 'warning' }}">{{ $score->status->label() }}</span>
                        </div>
                    </div>
                    @if ($score->lines->isNotEmpty())
                        <div class="data-list">
                            @foreach ($score->lines as $line)
                                <div class="data-row">
                                    <div>
                                        <strong>{{ $line->scoreObject?->name ?? 'Kriteria' }}</strong>
                                        <span>×{{ $line->quantity }}</span>
                                    </div>
                                    <span class="status-badge purple">{{ $line->computed_points }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </section>
        @empty
            <section class="card reveal"><p>Belum ada run.</p></section>
        @endforelse

        <div class="actions">
            <a class="button secondary" href="{{ route('coach.results.index') }}">← Kembali</a>
        </div>

        <div class="mascot-message">
            <img src="{{ asset('assets/e638f.png') }}" alt="">
            <strong>Bukan hanya medali. Setiap eksperimen, perbaikan, dan kerja sama tim adalah pencapaianmu.</strong>
        </div>
    </div>
</main>
@endsection
