@extends('layouts.roborace-public')

@section('title', 'Verifikasi Sertifikat')

@section('content')
<div class="app lavender-page">
    <main class="archive-main">
        <x-roborace.page-hero
            title="Verifikasi sertifikat"
            description="Halaman resmi untuk memastikan keaslian sertifikat RoboRace."
            mascot="786e4.png" />

        <article class="card">
            @if ($certificate)
                <span class="badge badge-row resmi">✓ Sertifikat sah</span>
                <h2>{{ $certificate->type === 'achievement' ? ($certificate->award ?? 'Pencapaian') : 'Sertifikat Partisipasi' }}</h2>
                <div class="archive-row">
                    <strong>{{ $certificate->team?->name }}</strong>
                    <span>{{ $certificate->team?->school?->name }}</span>
                </div>
                <div class="archive-row">
                    <strong>{{ $certificate->category?->level->publicLabel() }} · {{ $certificate->category?->name }}</strong>
                    <span>{{ $certificate->category?->event?->name }}</span>
                </div>
                <div class="archive-row">
                    <strong>Diterbitkan {{ $certificate->issued_at?->translatedFormat('d M Y') }}</strong>
                    <span>Token {{ $certificate->qr_token }}</span>
                </div>
            @else
                <span class="badge badge-row">✕ Tidak ditemukan</span>
                <h2>Sertifikat tidak terverifikasi</h2>
                <p class="muted">Token yang Anda masukkan tidak cocok dengan sertifikat mana pun. Periksa kembali tautannya.</p>
            @endif
        </article>
    </main>
</div>
@endsection
