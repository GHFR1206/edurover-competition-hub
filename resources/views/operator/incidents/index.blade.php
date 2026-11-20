@extends('layouts.roborace-operator')

@section('title', 'Laporan Insiden')

@php($page = 'incidents')

@section('content')
<header class="hero" id="top">
    <div class="event-row">
        <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
        <span class="series-chip">Seri Pilot</span>
    </div>
    <div class="hero-content">
        <div>
            <h1>Catat gangguan, jaga arena.</h1>
            <p>Laporkan kendala teknis, robot, atau jaringan pada arena Anda agar panitia dapat menindaklanjuti.</p>
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
        <span class="eyebrow">LAPORKAN INSIDEN</span>
        <h2>Insiden baru</h2>
        <form method="POST" action="{{ route('operator.incidents.store') }}" class="issue-row">
            @csrf
            <label class="file-field">
                <span>Arena</span>
                <select name="arena_id" class="field-input" required>
                    @foreach ($arenas as $option)
                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="file-field">
                <span>Tingkat</span>
                <select name="severity" class="field-input" required>
                    <option value="info">Info</option>
                    <option value="warning" selected>Peringatan</option>
                    <option value="critical">Kritis</option>
                </select>
            </label>
            <label class="file-field">
                <span>Kategori</span>
                <select name="category" class="field-input">
                    <option value="technical">Teknis</option>
                    <option value="robot">Robot</option>
                    <option value="network">Jaringan</option>
                    <option value="other">Lainnya</option>
                </select>
            </label>
            <label class="file-field">
                <span>Judul</span>
                <input type="text" name="title" maxlength="255" class="field-input" placeholder="Sensor garis arena terputus" required>
            </label>
            <label class="file-field">
                <span>Keterangan (opsional)</span>
                <input type="text" name="description" maxlength="2000" class="field-input" placeholder="Detail singkat kejadian">
            </label>
            <div class="run-actions">
                <button class="chip active" type="submit">Laporkan insiden</button>
            </div>
        </form>
    </section>

    <section class="content-card reveal">
        <span class="eyebrow">RIWAYAT · {{ $incidents->count() }} INSIDEN</span>
        <h2>Insiden arena saya</h2>
        <div class="queue-list">
            @forelse ($incidents as $incident)
                <div class="queue-row">
                    <div class="queue-info">
                        <h3>{{ $incident->title }}</h3>
                        <p>
                            {{ $incident->arena?->name }} ·
                            {{ strtoupper($incident->severity) }} ·
                            {{ $incident->category ? ucfirst($incident->category) : 'Umum' }}
                            @if ($incident->run?->team) · Run {{ $incident->run->team->name }} @endif
                        </p>
                        @if ($incident->description)
                            <p>{{ $incident->description }}</p>
                        @endif
                    </div>
                    <div class="queue-meta">
                        <span class="status">{{ ucfirst($incident->status) }}</span>
                    </div>
                    @if ($incident->status !== 'resolved')
                        <form method="POST" action="{{ route('operator.incidents.update', $incident) }}" class="issue-row">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="field-input">
                                <option value="open" @selected($incident->status === 'open')>Terbuka</option>
                                <option value="investigating" @selected($incident->status === 'investigating')>Investigasi</option>
                                <option value="resolved" @selected($incident->status === 'resolved')>Selesai</option>
                            </select>
                            <input type="text" name="resolution" maxlength="2000" class="field-input" placeholder="Resolusi (opsional)">
                            <button class="chip" type="submit">Perbarui</button>
                        </form>
                    @else
                        <p class="muted">✓ Selesai @if ($incident->resolution) · {{ $incident->resolution }} @endif</p>
                    @endif
                </div>
            @empty
                <p class="empty-list">Belum ada insiden dilaporkan. Semoga arena tetap lancar!</p>
            @endforelse
        </div>
    </section>

    <div class="mascot-message">
        <img src="{{ asset('assets/dc500.png') }}" alt="">
        <strong>Operator menjaga ritme arena. Laporan insiden tercatat di audit log untuk tindak lanjut panitia.</strong>
    </div>
</main>
@endsection
