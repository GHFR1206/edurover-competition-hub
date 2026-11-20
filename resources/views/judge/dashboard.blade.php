@extends('layouts.roborace-juri')

@section('title', 'Suara Remote')

@php($page = 'remote')

@section('content')
<div class="page light">
    <header class="page-hero">
        <div>
            <div class="event-row">
                <span>● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
                <b>Seri Pilot</b>
            </div>
            <h1>Satu suara, untuk lomba adil.</h1>
            <p>{{ auth()->user()->name }} · Siap menilai pencapaian robot di arena Anda.</p>
        </div>
        <img class="hero-mascot" src="{{ asset('assets/d8b40.png') }}" alt="Maskot RoboRace">
    </header>
    <main class="page-content remote-content">
        <section class="card achievement-card">
            <code>RINGKASAN TUGAS ANDA</code>
            <h2>Pencapaian dari robot</h2>
            <p class="muted">
                Nilai setiap pencapaian berdasarkan bukti arena. Ringkasan ini menampilkan jumlah run
                di arena Anda, run siap dinilai, dan skor yang sudah Anda ajukan.
            </p>
            <div class="checkpoints">
                <article class="checkpoint">
                    <div>
                        <h3>Run di Arena Anda · {{ $assigned }}</h3>
                        <span>Total run yang ditugaskan kepada Anda</span>
                    </div>
                    <em class="neutral">{{ $assigned }} run</em>
                    <div class="progress"><i style="width:100%"></i></div>
                </article>
                <article class="checkpoint">
                    <div>
                        <h3>Siap Dinilai · {{ $pending }}</h3>
                        <span>Run selesai yang menunggu penilaian Anda</span>
                    </div>
                    <em class="{{ $pending > 0 ? 'warning' : 'success' }}">{{ $pending > 0 ? 'Perlu dinilai' : '✓ Tuntas' }}</em>
                    <div class="progress"><i style="width:{{ $assigned > 0 ? round($pending / max($assigned, 1) * 100) : 0 }}%"></i></div>
                </article>
                <article class="checkpoint">
                    <div>
                        <h3>Skor Diajukan · {{ $submitted }}</h3>
                        <span>Skor yang sudah Anda kirim ke Chief Judge</span>
                    </div>
                    <em class="success">✓ Terkirim</em>
                    <div class="progress"><i style="width:100%"></i></div>
                </article>
            </div>
        </section>

        <div class="yellow-note">
            Poin belum masuk total sampai Chief Judge mengambil keputusan. Tugas hanya muncul untuk
            arena tempat Anda aktif sebagai juri.
        </div>

        <section class="card vote-record">
            <h2>Mulai menilai</h2>
            <p>Buka daftar tugas untuk melihat run di arena Anda dan mulai mengisi penilaian.</p>
            <small>Jejak suara menyimpan pelaku, waktu, objek pencapaian, dan perubahan untuk audit.</small>
            <a class="chip active" href="{{ route('judge.tasks.index') }}">Lihat Tugas Penilaian</a>
        </section>

        <div class="mascot-message">
            <img src="{{ asset('assets/d8b40.png') }}" alt="">
            <b>Mayoritas bukan keputusan akhir. Chief akan memeriksa bukti sebelum mengesahkan atau meninjau ulang.</b>
        </div>
    </main>
</div>
@endsection
