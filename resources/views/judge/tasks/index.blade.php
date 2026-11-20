@extends('layouts.roborace-juri')

@section('title', 'Tugas Penilaian')

@php($page = 'remote')

@section('content')
<div class="page light">
    <header class="page-hero">
        <div>
            <div class="event-row">
                <span>● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
                <b>Seri Pilot</b>
            </div>
            <h1>Pencapaian dari robot.</h1>
            <p>{{ auth()->user()->name }} · Pilih run di arena Anda untuk memberi penilaian.</p>
        </div>
        <img class="hero-mascot" src="{{ asset('assets/d8b40.png') }}" alt="Maskot RoboRace">
    </header>
    <main class="page-content remote-content">
        <section class="card achievement-card">
            <code>ANTREAN RUN · {{ $runs->count() }} RUN</code>
            <h2>Daftar tugas</h2>
            <p class="muted">Hanya run pada arena tempat Anda ditugaskan dan aktif yang muncul di sini.</p>
            <div class="checkpoints">
                @forelse ($runs as $run)
                    @php($mine = $run->scores->firstWhere('judge_id', auth()->id()))
                    <article class="checkpoint">
                        <div>
                            <h3>{{ $run->team?->name }} · {{ $run->arena?->name }}</h3>
                            <span>
                                {{ $run->team?->school?->name }} ·
                                {{ $run->category?->level->publicLabel() }} ·
                                Status run: {{ $run->status->label() }}
                            </span>
                        </div>
                        <em class="{{ $mine?->status->value === 'approved' ? 'success' : ($mine ? 'warning' : 'neutral') }}">
                            {{ $mine?->status->label() ?? 'Belum dinilai' }}
                        </em>
                        <div class="progress"><i style="width:{{ $mine ? 100 : 0 }}%"></i></div>
                        <a class="chip {{ $mine ? '' : 'active' }}" href="{{ route('judge.scores.edit', $run) }}">
                            {{ $mine ? 'Ubah penilaian' : 'Nilai sekarang' }}
                        </a>
                    </article>
                @empty
                    <article class="checkpoint">
                        <div>
                            <h3>Belum ada run</h3>
                            <span>Belum ada run di arena Anda saat ini.</span>
                        </div>
                        <em class="neutral">Kosong</em>
                    </article>
                @endforelse
            </div>
        </section>

        <div class="yellow-note">
            Simpan draft terlebih dahulu, lalu ajukan ke Chief Judge saat penilaian sudah lengkap.
        </div>

        <div class="mascot-message">
            <img src="{{ asset('assets/d8b40.png') }}" alt="">
            <b>Mayoritas bukan keputusan akhir. Chief akan memeriksa bukti sebelum mengesahkan atau meninjau ulang.</b>
        </div>
    </main>
</div>
@endsection
