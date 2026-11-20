@extends('layouts.roborace-event-admin')

@section('title', 'Dashboard')
@php($page = 'events')
@section('hero-title', 'Rancang arena yang hebat.')
@section('hero-subtitle', 'Kelola event dan tahap kompetisi, dari ide sampai garis finis.')

@section('content')
<div class="stats">
    <section class="card stat">
        <span class="eyebrow">TIM TERDAFTAR</span>
        <strong>{{ $teamCount }}</strong>
        <small>{{ $arenaCount }} arena · {{ $categoryCount }} kategori</small>
        <i></i>
    </section>
    <section class="card stat">
        <span class="eyebrow">RUN TERCATAT</span>
        <strong>{{ $runCount }}</strong>
        <small>Sesi penyisihan seluruh arena</small>
        <i></i>
    </section>
    <section class="card stat">
        <span class="eyebrow">PENDAFTARAN DITINJAU</span>
        <strong>{{ $pendingRegistrations }}</strong>
        <small>Menunggu verifikasi Super Admin</small>
        <i></i>
    </section>
</div>

<section class="card">
    <span class="eyebrow">EVENT AKTIF</span>
    <h2>{{ $event?->name ?? 'Belum ada event' }}</h2>
    @if ($event)
        <p class="muted">
            {{ $event->venue ?? 'Lokasi belum diisi' }} ·
            {{ $event->starts_at?->translatedFormat('d M Y') }}–{{ $event->ends_at?->translatedFormat('d M Y') }}
            · Status {{ $event->status }}
        </p>
        <div class="row-actions">
            <a class="button" href="{{ route('eventadmin.events.index') }}">Kelola event</a>
            <a class="button" href="{{ route('eventadmin.setup.index') }}">Atur setup</a>
            <a class="button yellow" href="{{ route('eventadmin.schedule.index') }}">Jadwal &amp; arena</a>
        </div>
    @else
        <p class="muted">Buat event pertama melalui menu Event &amp; Ruleset.</p>
    @endif
</section>

<section class="card">
    <h2>Panel staf</h2>
    <div class="data-row"><div><strong>Juri terdaftar</strong><span>Siap ditugaskan ke arena</span></div><span class="badge">{{ $judgeCount }}</span></div>
    <div class="data-row"><div><strong>Arena</strong><span>Kapasitas satu tim per slot</span></div><span class="badge">{{ $arenaCount }}</span></div>
    <div class="data-row"><div><strong>Kategori</strong><span>Jenjang yang dipertandingkan</span></div><span class="badge">{{ $categoryCount }}</span></div>
</section>
@endsection
