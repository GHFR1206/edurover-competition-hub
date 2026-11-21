@extends('layouts.roborace-operator')

@section('title', $arena->name)

@php
    $statusClass = [
        'scheduled' => 'status-menunggu',
        'called' => 'status-dipanggil',
        'ready' => 'status-dipanggil',
        'running' => 'status-berjalan',
        'finished' => 'status',
        'dnf' => 'status',
        'dq' => 'status',
        'no_show' => 'status',
        're_run' => 'status-dipanggil',
    ];
@endphp
@php($page = 'operator')

@section('content')
<header class="hero" id="top">
    <div class="event-row">
        <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
        <span class="series-chip">Seri Pilot</span>
    </div>
    <div class="hero-content">
        <div>
            <h1>Kontrol {{ $arena->name }}</h1>
            <p>{{ $arena->code ?? '—' }} · {{ $arena->location ?? 'Lokasi belum diisi' }} · {{ $runs->count() }} run</p>
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
        <a class="chip" href="{{ route('operator.arenas.index') }}">← Kembali</a>
        <span class="chip active">{{ ucfirst($arena->status) }}</span>
    </div>

    <section class="content-card reveal">
        <span class="eyebrow">ANTREAN RUN · {{ $runs->count() }}</span>
        <h2>Status run</h2>
        <div class="queue-list">
            @forelse ($runs as $run)
                <div class="queue-row">
                    <div class="queue-info">
                        <h3>{{ $run->team?->name }} · {{ $run->category?->level->publicLabel() }}</h3>
                        <p>{{ $run->team?->school?->name }} · {{ $run->scheduled_at?->translatedFormat('d M Y, H:i') ?? 'Belum dijadwalkan' }} WIB</p>
                    </div>
                    <div class="queue-meta">
                        <span class="status {{ $statusClass[$run->status->value] ?? '' }}">{{ $run->status->label() }}</span>
                        @if ($run->official_time_ms !== null)
                            <span class="status status-berjalan" title="Waktu resmi dari perangkat arena">
                                ⏱ {{ \App\Services\StandingsService::formatMs($run->official_time_ms) }}
                                @if ($run->lap_count) · {{ $run->lap_count }} lap @endif
                            </span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('operator.runs.status', $run) }}" class="issue-row">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="field-input">
                            @foreach (\App\Enums\RunStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($run->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="outcome_reason" placeholder="Alasan (opsional)" class="field-input">
                        <button class="chip" type="submit">Simpan</button>
                    </form>
                </div>
            @empty
                <p class="empty-list">Belum ada run di arena ini.</p>
            @endforelse
        </div>
    </section>

    <div class="mascot-message">
        <img src="{{ asset('assets/dc500.png') }}" alt="">
        <strong>Perubahan status run tercatat di audit log. Keputusan poin tetap berada di tangan juri dan Chief Judge.</strong>
    </div>
</main>
@endsection
