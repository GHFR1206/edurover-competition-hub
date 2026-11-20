@extends('layouts.roborace-chief')

@section('title', 'Review & Publikasi')

@php($page = 'review')

@section('hero')@endsection
@section('hero-title', 'Valid bukan berarti sudah resmi.')
@section('hero-subtitle', 'Review penilaian, sahkan, lalu publikasikan hasil setiap level.')
@section('hero-mascot', '786e4.png')

@section('content')
<div class="stats">
    <section class="card">
        <span class="eyebrow">MENUNGGU VERIFIKASI</span>
        <b>{{ str_pad($submitted, 2, '0', STR_PAD_LEFT) }}</b>
        <p>Skor juri menunggu keputusan Anda</p>
        <i><img src="{{ asset('assets/33664.svg') }}" alt=""></i>
    </section>
    <section class="card">
        <span class="eyebrow">DIBUKA ULANG</span>
        <b>{{ str_pad($reopened, 2, '0', STR_PAD_LEFT) }}</b>
        <p>Skor yang perlu ditinjau kembali</p>
        <i><img src="{{ asset('assets/33664.svg') }}" alt=""></i>
    </section>
    <section class="card">
        <span class="eyebrow">SUDAH DISETUJUI</span>
        <b>{{ str_pad($approved, 2, '0', STR_PAD_LEFT) }}</b>
        <p>Skor resmi terverifikasi</p>
        <i><img src="{{ asset('assets/33664.svg') }}" alt=""></i>
    </section>
</div>

<section class="card">
    <span class="eyebrow purple">MAKER-CHECKER</span>
    <h2>Keputusan Anda</h2>
    <p>Anda tidak dapat menyetujui skor yang Anda tulis sendiri. Telusuri antrean verifikasi untuk memulai.</p>
    <div class="actions">
        <a class="button primary" href="{{ route('chief.verification.index') }}">Verifikasi skor</a>
        <a class="button" href="{{ route('chief.publication.index') }}">Publikasi hasil</a>
    </div>
</section>

<div class="mascot-note">
    <img src="{{ asset('assets/d8b40.png') }}" alt="Maskot RoboRace">
    <strong>Valid bukan berarti sudah Resmi. Tanda ✓ Resmi muncul hanya setelah level dipublikasikan.</strong>
</div>
@endsection
