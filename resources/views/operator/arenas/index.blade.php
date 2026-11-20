@extends('layouts.roborace-operator')

@section('title', 'Arena Saya')

@php($page = 'operator')

@section('content')
<header class="hero" id="top">
    <div class="event-row">
        <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
        <span class="series-chip">Seri Pilot</span>
    </div>
    <div class="hero-content">
        <div>
            <h1>Arena yang kamu pegang.</h1>
            <p>Pilih arena untuk mengatur antrean dan status run.</p>
        </div>
        <img class="hero-mascot" src="{{ asset('assets/dc500.png') }}" alt="Maskot RoboRace">
    </div>
</header>

<main class="main-content">
    <div class="ornament gear">✿</div>
    <div class="ornament flag">▦</div>
    <div class="ornament hexagon"></div>
    <div class="ornament triangle"></div>

    <section class="content-card reveal">
        <span class="eyebrow">DAFTAR ARENA · {{ $arenas->count() }}</span>
        <h2>Arena saya</h2>
        <div class="queue-list">
            @forelse ($arenas as $arena)
                <div class="queue-row">
                    <div class="queue-info">
                        <h3>{{ $arena->name }}</h3>
                        <p>{{ $arena->code ?? '—' }} · {{ $arena->location ?? 'Lokasi belum diisi' }}</p>
                    </div>
                    <div class="queue-meta">
                        <span class="status">{{ ucfirst($arena->status) }}</span>
                        <a class="chip" href="{{ route('operator.arenas.show', $arena) }}">Buka kontrol</a>
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
