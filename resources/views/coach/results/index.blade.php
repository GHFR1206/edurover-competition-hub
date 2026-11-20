@extends('layouts.roborace-coach')

@section('title', 'Hasil')

@php($page = 'results')

@section('content')
<section class="hero">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>Kerja kerasmu, tercatat.</h1>
                <p>Rekap hasil dan sertifikat tim sekolahmu.</p>
            </div>
            <img class="hero-mascot" src="{{ asset('assets/786e4.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="ornament" aria-hidden="true">
        <img src="{{ asset('assets/98d5a.svg') }}" alt="">
        <span class="gear-cut"><img src="{{ asset('assets/98d5a.svg') }}" alt=""></span>
    </div>
    <div class="content-width page-stack">
        <div class="notice warning">
            Skor <strong>Diajukan (belum resmi)</strong> masih dapat berubah. Skor <strong>Disetujui</strong> bersifat resmi. Nilai <strong>Draft</strong> tidak pernah ditampilkan kepada coach.
        </div>

        <section class="card reveal">
            <span class="eyebrow">PILIH TIM</span>
            <h2>Hasil tim saya</h2>
            <div class="data-list">
                @forelse ($teams as $team)
                    <div class="data-row">
                        <div>
                            <strong>{{ $team->name }}</strong>
                            <span>{{ $team->category?->level->publicLabel() }} — {{ $team->school?->name }}</span>
                        </div>
                        <a class="button secondary" href="{{ route('coach.results.show', $team) }}">Lihat hasil</a>
                    </div>
                @empty
                    <p>Belum ada tim.</p>
                @endforelse
            </div>
        </section>
    </div>
</main>
@endsection
