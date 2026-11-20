@extends('layouts.roborace-chief')

@section('title', 'Verifikasi Skor')

@php($page = 'review')

@section('hero')@endsection
@section('hero-title', 'Tinjau sebelum mengesahkan.')
@section('hero-subtitle', 'Setiap skor juri melewati pemeriksaan Chief Judge. Maker-checker dijaga ketat.')
@section('hero-mascot', 'd8b40.png')

@section('content')
<div class="warning">
    <strong>Maker-checker:</strong> Anda tidak dapat menyetujui skor yang Anda tulis sendiri.
</div>

<section class="card">
    <span class="eyebrow">ANTREAN VERSIFIKASI · {{ $scores->count() }}</span>
    <h2>Skor menunggu keputusan</h2>
    <div class="rows">
        @forelse ($scores as $score)
            <div class="data-row">
                <div>
                    <strong>{{ $score->run?->team?->name }} · {{ $score->run?->category?->level->publicLabel() }}</strong>
                    <span>{{ $score->run?->arena?->name }} · Juri {{ $score->judge?->name }} · {{ $score->status->label() }}</span>
                </div>
                <div class="score">{{ $score->total }}</div>
                <span class="badge {{ $score->status->value === 'reopened' ? 'yellow' : '' }}">{{ $score->status->label() }}</span>
                <a class="button" href="{{ route('chief.verification.show', $score) }}">Tinjau</a>
            </div>
        @empty
            <p class="muted">Tidak ada skor menunggu verifikasi.</p>
        @endforelse
    </div>
</section>

<div class="mascot-note">
    <img src="{{ asset('assets/d8b40.png') }}" alt="Maskot RoboRace">
    <strong>Valid bukan berarti sudah Resmi. Tanda ✓ Resmi muncul hanya setelah level dipublikasikan.</strong>
</div>
@endsection
