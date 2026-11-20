@extends('layouts.roborace-event-admin')

@section('title', 'Laporan')
@php($page = 'reports')
@section('hero-title', 'Cerita lomba dalam angka.')
@section('hero-subtitle', ($event?->name ?? 'Belum ada event') . ' · Ringkasan panitia dan unduhan.')

@section('content')
<div class="stats">
    <section class="card stat">
        <span class="eyebrow">TIM TERDAFTAR</span>
        <strong>{{ $teamCount }}</strong>
        <small>{{ $verifiedCount }} terverifikasi · {{ $reviewCount }} ditinjau</small>
        <i></i>
    </section>
    <section class="card stat">
        <span class="eyebrow">SEKOLAH</span>
        <strong>{{ $schoolCount }}</strong>
        <small>Asal sekolah peserta event ini</small>
        <i></i>
    </section>
    <section class="card stat">
        <span class="eyebrow">RUN SELESAI</span>
        <strong>{{ $runFinished }}</strong>
        <small>Dari {{ $runTotal }} run terjadwal</small>
        <i></i>
    </section>
</div>

<section class="card">
    <h2>Daftar tim &amp; sekolah</h2>
    <div class="rows">
        <div class="row">
            <div class="row-copy"><strong>Daftar peserta lengkap</strong><span>{{ $teamCount }} tim · nama anggota, jenjang, status verifikasi</span></div>
            <span class="badge">CSV</span>
        </div>
        <div class="row">
            <div class="row-copy"><strong>Rekap sekolah</strong><span>{{ $schoolCount }} sekolah · coach pendamping dan kontak</span></div>
            <span class="badge">CSV</span>
        </div>
        <div class="row">
            <div class="row-copy"><strong>Rekap kehadiran arena</strong><span>{{ $runFinished }} selesai · dari {{ $runTotal }} run</span></div>
            <span class="badge">CSV</span>
        </div>
    </div>
</section>

<section class="card">
    <span class="eyebrow">HANYA SETELAH DIPUBLIKASIKAN</span>
    <h2>Hasil resmi</h2>
    <div class="rows">
        @forelse ($published as $category)
            <div class="row">
                <div class="row-copy">
                    <strong>{{ $category->level->publicLabel() }} · Hasil sudah dipublikasikan</strong>
                    <span>{{ $category->name }} · {{ $category->publication?->published_at?->translatedFormat('d M Y, H:i') ?? 'Dipublikasikan' }} WIB</span>
                </div>
                <div class="row-actions">
                    <span class="badge badge-✓-resmi">✓ Resmi</span>
                    <a class="button" href="{{ route('public.results.category', $category) }}">Lihat</a>
                </div>
            </div>
        @empty
            <p class="muted">Belum ada level yang dipublikasikan.</p>
        @endforelse

        @foreach ($draftLevels as $category)
            <div class="event-entry">
                <div class="row">
                    <div class="row-copy">
                        <strong>{{ $category->level->publicLabel() }} · Menunggu publikasi</strong>
                        <span>Penilaian masih ditinjau oleh Chief Judge</span>
                    </div>
                    <span class="badge badge-◔-sementara">◔ Sementara</span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="card">
    <h2>Sekolah paling aktif</h2>
    <div class="rows">
        @forelse ($schools as $school)
            <div class="row">
                <div class="row-copy"><strong>{{ $school->name }}</strong><span>{{ $school->city ?? 'Kota belum diisi' }}</span></div>
                <span class="badge">{{ $school->teams_count }} tim</span>
            </div>
        @empty
            <p class="muted">Belum ada data sekolah.</p>
        @endforelse
    </div>
</section>
@endsection
