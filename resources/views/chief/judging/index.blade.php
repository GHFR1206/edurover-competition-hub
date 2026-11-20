@extends('layouts.roborace-chief')

@section('title', 'Tinjau Penilaian')

@php($page = 'judging')

@section('hero')@endsection
@section('hero-title', 'Periksa bukti, baru sahkan.')
@section('hero-subtitle', 'Tinjau setiap penilaian juri beserta bukti arena sebelum mengambil keputusan.')
@section('hero-mascot', 'd8b40.png')

@section('content')
<div class="warning">
    Hanya skor berstatus <strong>Diajukan</strong> atau <strong>Dibuka Ulang</strong> yang tampil di sini. Nilai <strong>Draft</strong> tidak pernah ditinjau.
</div>

@forelse ($scores as $score)
    <section class="card">
        <span class="eyebrow purple">{{ strtoupper($score->run?->category?->level->publicLabel() ?? '') }} · {{ $score->run?->arena?->name }}</span>
        <h2>{{ $score->run?->team?->name }} · {{ $score->run?->category?->level->publicLabel() }}</h2>
        <div class="badge yellow">{{ $score->status->label() }}</div>

        <div class="rows">
            <div class="data-row">
                <div>
                    <strong>Juri penulis</strong>
                    <span>{{ $score->judge?->name }} · versi {{ $score->version }} · diajukan {{ $score->submitted_at?->translatedFormat('d M Y H:i') }} WIB</span>
                </div>
                <div class="score">{{ $score->total }}</div>
            </div>
            @foreach ($score->lines as $line)
                <div class="data-row">
                    <div>
                        <strong>{{ $line->scoreObject?->name }}</strong>
                        <span>×{{ $line->quantity }} @if ($line->achieved) · ✓ tercapai @endif @if ($line->notes) · {{ $line->notes }} @endif</span>
                    </div>
                    <div class="score">{{ $line->computed_points }}</div>
                </div>
            @endforeach
        </div>

        @if ($score->run?->evidenceFiles?->isNotEmpty())
            <div class="evidence">
                @foreach ($score->run->evidenceFiles as $file)
                    <a href="{{ route('evidence.show', $file) }}" target="_blank" rel="noopener">{{ $file->original_name ?? 'bukti' }}</a>
                @endforeach
            </div>
        @else
            <p class="muted">Tidak ada bukti terunggah untuk run ini.</p>
        @endif

        <div class="actions">
            <a class="button primary" href="{{ route('chief.verification.show', $score) }}">Buka keputusan</a>
        </div>
    </section>
@empty
    <section class="card">
        <p class="muted">Tidak ada penilaian yang menunggu ditinjau. Semua sudah beres!</p>
    </section>
@endforelse
@endsection
