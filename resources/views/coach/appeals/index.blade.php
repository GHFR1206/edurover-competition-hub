@extends('layouts.roborace-coach')

@section('title', 'Banding')

@php($page = 'appeals')

@section('content')
<section class="hero hero-schedule">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>Ajukan banding dengan tenang.</h1>
                <p>Jika ada skor yang menurutmu tidak sesuai, ajukan banding beserta alasannya. Chief Judge akan meninjau.</p>
            </div>
            <img class="hero-mascot schedule-mascot" src="{{ asset('assets/786e4.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="content-width page-stack">

        <div class="notice warning">
            Banding hanya untuk run milik tim sekolahmu. Satu run hanya boleh memiliki satu banding terbuka pada satu waktu.
        </div>

        <section class="card reveal">
            <span class="eyebrow">AJUKAN BANDING</span>
            <h2>Banding baru</h2>
            <form method="POST" action="{{ route('coach.appeals.store') }}">
                @csrf
                <div class="form-grid">
                    <label class="field">
                        <span>Run / Arena</span>
                        <select name="run_id" required>
                            @foreach ($teams as $team)
                                @foreach ($team->runs as $run)
                                    <option value="{{ $run->id }}">
                                        {{ $team->name }} · {{ $run->arena?->name }} · {{ $run->status->label() }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </label>
                </div>
                <label class="field">
                    <span>Alasan banding *</span>
                    <textarea name="reason" rows="4" required maxlength="2000" placeholder="Jelaskan bagian mana yang tidak sesuai dan sertakan bukti bila ada..."></textarea>
                </label>
                <div class="actions">
                    <button class="button primary" type="submit">Kirim banding</button>
                </div>
            </form>
        </section>

        <section class="card reveal">
            <span class="eyebrow">RIWAYAT · {{ $appeals->count() }} BANDING</span>
            <h2>Banding saya</h2>
            <div class="data-list">
                @forelse ($appeals as $appeal)
                    <div class="data-row">
                        <div>
                            <strong>{{ $appeal->team?->name }} · {{ $appeal->run?->arena?->name }}</strong>
                            <span>{{ $appeal->reason }}</span>
                            @if ($appeal->resolution)
                                <span>Keputusan: {{ $appeal->resolution }}</span>
                            @endif
                        </div>
                        <span class="status-badge {{ $appeal->status === 'accepted' ? 'success' : ($appeal->status === 'rejected' ? 'warning' : 'purple') }}">
                            {{ ucfirst(str_replace('_', ' ', $appeal->status)) }}
                        </span>
                    </div>
                @empty
                    <p>Belum ada banding diajukan.</p>
                @endforelse
            </div>
        </section>

        <div class="mascot-message">
            <img src="{{ asset('assets/e638f.png') }}" alt="">
            <strong>Banding adalah hakmu. Sampaikan dengan data dan bukti agar ditinjau lebih cepat.</strong>
        </div>
    </div>
</main>
@endsection
