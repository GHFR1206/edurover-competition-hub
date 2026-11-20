@extends('layouts.roborace-juri')

@section('title', 'Nilai Manual')

@php($page = 'manual')

@section('content')
<div class="page light">
    <header class="page-hero">
        <div>
            <div class="event-row">
                <span>● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
                <b>Seri Pilot</b>
            </div>
            <h1>Amati detail. Nilai dengan adil.</h1>
            <p>{{ auth()->user()->name }} · Skor yang Anda tulis sendiri, juri lain tidak ditampilkan.</p>
        </div>
        <img class="hero-mascot" src="{{ asset('assets/17d7a.png') }}" alt="Maskot RoboRace">
    </header>
    <main class="manual-space">
        <section class="card guide">
            <div>
                <i>1</i>
                <span>
                    <b>Anda hanya melihat skor Anda</b>
                    <p>Skor juri lain tidak ditampilkan demi menjaga independensi penilaian.</p>
                </span>
            </div>
            <div>
                <i>2</i>
                <span>
                    <b>Simpan draft sebelum mengirim</b>
                    <p>Draft dapat diubah kapan saja. Setelah diajukan, hanya Chief Judge yang dapat membuka kembali.</p>
                </span>
            </div>
        </section>

        <section class="card recap standalone">
            <span class="eyebrow" style="color:#8a68d4;font:11px 'JetBrains Mono'">SKOR YANG SAYA TULIS</span>
            <h2>Skor saya</h2>
            @forelse ($scores as $score)
                <div>
                    <span>
                        <b>{{ $score->run?->team?->name }} · {{ $score->run?->arena?->name }}</b>
                        <small>{{ $score->status->label() }}</small>
                    </span>
                    <strong>{{ $score->total }}</strong>
                    <em class="{{ $score->status->value === 'approved' ? '' : 'submitted' }}">{{ $score->status->label() }}</em>
                </div>
            @empty
                <p class="muted">Belum ada skor.</p>
            @endforelse
        </section>

        <section class="card vote-record">
            <h2>Lanjutkan penilaian</h2>
            <p>Buka tugas untuk mengisi atau melanjutkan draft penilaian Anda.</p>
            <a class="chip active" href="{{ route('judge.tasks.index') }}">Lihat Tugas Penilaian</a>
        </section>
    </main>
</div>
@endsection
