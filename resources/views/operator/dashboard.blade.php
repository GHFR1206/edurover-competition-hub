@extends('layouts.roborace-operator')

@section('title', 'Dashboard Operator')

@php($page = 'operator')

@section('content')
<header class="hero" id="top">
    <div class="event-row">
        <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
        <span class="series-chip">Seri Pilot</span>
    </div>
    <div class="hero-content">
        <div>
            <h1>Jaga ritme. Nyalakan arena.</h1>
            <p>{{ auth()->user()->name }} · Pantau run aktif dan kendalikan arena yang Anda tugaskan.</p>
        </div>
        <img class="hero-mascot" src="{{ asset('assets/dc500.png') }}" alt="Maskot RoboRace">
    </div>
</header>

<main class="main-content">
    <div class="ornament gear">✿</div>
    <div class="ornament flag">▦</div>
    <div class="ornament hexagon"></div>
    <div class="ornament triangle"></div>

    <div class="arena-tabs">
        <span class="chip active">Arena Ditugaskan · {{ $arenas->count() }}</span>
        <a class="chip" href="{{ route('operator.arenas.index') }}">Buka daftar arena</a>
    </div>

    <section class="run-card reveal">
        @if ($activeRuns->isEmpty())
            <div class="empty-run">
                <span class="run-label">RUN ARENA</span>
                <h2>Belum ada run berjalan.</h2>
                <p>Pilih tim berstatus Siap mulai dari antrean sesi arena.</p>
            </div>
        @else
            <span class="run-label">● {{ $activeRuns->count() }} RUN AKTIF</span>
            <div class="run-grid">
                @foreach ($activeRuns as $run)
                    <div class="active-robot">
                        <h2>{{ $run->team?->name }} · {{ $run->arena?->name }}</h2>
                        <p>{{ $run->team?->school?->name }} · {{ $run->category?->level->publicLabel() }}</p>
                        <div class="timer">{{ $run->status->label() }}</div>
                    </div>
                @endforeach
            </div>
            <div class="run-actions">
                <a class="button primary" href="{{ route('operator.arenas.index') }}">Kontrol arena</a>
            </div>
        @endif
    </section>

    <section class="content-card reveal">
        <span class="eyebrow">RINGKASAN</span>
        <h2>Arena saya</h2>
        <div class="queue-list">
            @forelse ($arenas as $arena)
                <div class="queue-row">
                    <div class="queue-info">
                        <h3>{{ $arena->name }}</h3>
                        <p>{{ $arena->location ?? 'Lokasi belum diisi' }} · {{ $arena->event?->name ?? 'Event' }}</p>
                    </div>
                    <div class="queue-meta">
                        <span class="status">{{ ucfirst($arena->status) }}</span>
                        <a class="chip" href="{{ route('operator.arenas.show', $arena) }}">Buka</a>
                    </div>
                </div>
            @empty
                <p class="empty-list">Anda belum ditugaskan ke arena mana pun.</p>
            @endforelse
        </div>
    </section>

    <div class="mascot-message">
        <img src="{{ asset('assets/dc500.png') }}" alt="">
        <strong>Operator menjaga ritme arena. Keputusan poin tetap berada di tangan juri dan Chief Judge.</strong>
    </div>
</main>
@endsection
