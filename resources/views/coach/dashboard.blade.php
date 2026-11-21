@extends('layouts.roborace-coach')

@section('title', 'Tim Saya')

@php($page = 'team')

@section('content')
<section class="hero">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>Tim kecil, mimpi besar.</h1>
                <p>Halo, {{ auth()->user()->name }}! Siapkan timmu untuk petualangan berikutnya.</p>
            </div>
            <img class="hero-mascot" src="{{ asset('assets/e638f.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="ornament" aria-hidden="true">
        <img src="{{ asset('assets/98d5a.svg') }}" alt="">
        <span class="gear-cut"><img src="{{ asset('assets/98d5a.svg') }}" alt=""></span>
    </div>
    <div class="content-width page-stack">
        <div class="stats-grid">
            <section class="card stat-card reveal" style="animation-delay:40ms">
                <span class="antenna"><img src="{{ asset('assets/33664.svg') }}" alt=""></span>
                <span class="eyebrow neutral">TIM TERDAFTAR</span>
                <b class="stat-number">{{ $teams->count() }}</b>
                <p>Tim sekolah Anda di kompetisi ini</p>
            </section>
            <section class="card stat-card reveal" style="animation-delay:110ms">
                <span class="antenna"><img src="{{ asset('assets/33664.svg') }}" alt=""></span>
                <span class="eyebrow neutral">SKOR MENUNGGU</span>
                <b class="stat-number">{{ $pending }}</b>
                <p>Penilaian menunggu verifikasi juri</p>
            </section>
            <section class="card stat-card reveal" style="animation-delay:180ms">
                <span class="antenna"><img src="{{ asset('assets/33664.svg') }}" alt=""></span>
                <span class="eyebrow neutral">SEKOLAH</span>
                <b class="stat-number" style="font-size:30px;line-height:1.15">{{ auth()->user()->coachProfile?->school?->name ?? '—' }}</b>
                <p>Asal sekolah tim binaan Anda</p>
            </section>
        </div>

        <section class="card reveal" style="animation-delay:240ms">
            <span class="eyebrow">DAFTAR TIM</span>
            <h2>Tim saya</h2>
            <div class="data-list">
                @forelse ($teams as $team)
                    <div class="data-row">
                        <div>
                            <strong>{{ $team->name }}</strong>
                            <span>{{ $team->category?->level->publicLabel() }} · {{ $team->category?->name }} · {{ $team->runs->count() }} run</span>
                        </div>
                        <a class="button secondary" href="{{ route('coach.teams.show', $team) }}">Lihat tim</a>
                    </div>
                @empty
                    <p>Belum ada tim terdaftar untuk sekolah Anda.</p>
                @endforelse
            </div>
            <div class="actions">
                <a class="button primary" href="{{ route('coach.results.index') }}">Lihat hasil</a>
                <a class="button secondary" href="{{ route('coach.teams.create') }}">+ Daftarkan tim</a>
            </div>
        </section>

        <div class="mascot-message">
            <img src="{{ asset('assets/d8b40.png') }}" alt="">
            <strong>Robot &amp; API key baru tersedia setelah tim terverifikasi. Perbaiki catatan reviewer, lalu kirim ulang datamu.</strong>
        </div>
    </div>
</main>
@endsection
