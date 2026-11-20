<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->type === 'achievement' ? ($certificate->award ?? 'Piagam') : 'Sertifikat Partisipasi' }} · RoboRace</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap">
    @vite(['resources/css/roborace/public.css'])
    @stack('styles')
</head>
<body>
<div class="certificate-page">
    <div class="certificate-card">
        <div class="certificate-brand">Robo<span>Race</span></div>

        <span class="eyebrow">{{ $certificate->type === 'achievement' ? 'PIAGAM PENCAPAIAN' : 'SERTIFIKAT PARTISIPASI' }}</span>
        <h1>{{ $certificate->type === 'achievement' ? ($certificate->award ?? 'Pencapaian') : 'Sertifikat Partisipasi' }}</h1>

        <p class="muted">Diberikan kepada</p>
        <h2 class="certificate-name">{{ $certificate->team?->name }}</h2>
        <p class="muted">{{ $certificate->team?->school?->name }}</p>

        <div class="certificate-meta">
            <div><strong>{{ $certificate->category?->event?->name }}</strong><span>Event</span></div>
            <div><strong>{{ $certificate->category?->level->publicLabel() }} · {{ $certificate->category?->name }}</strong><span>Kategori</span></div>
            <div><strong>{{ $certificate->issued_at?->translatedFormat('d M Y') }}</strong><span>Tanggal terbit</span></div>
        </div>

        <div class="certificate-verify">
            <span class="eyebrow">VERIFIKASI</span>
            <code>{{ $certificate->qr_token }}</code>
            <small>Pindai/verifikasi di {{ route('certificates.verify', $certificate->qr_token) }}</small>
        </div>

        <div class="certificate-actions">
            <button class="purple-btn" type="button" onclick="window.print()">Cetak / Simpan PDF</button>
            <a class="outline-btn" href="{{ url('/') }}">Kembali ke beranda</a>
        </div>
    </div>
</div>
</body>
</html>
