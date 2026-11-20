@extends('layouts.roborace-public')

@section('title', 'RoboRace · Jadwal')

@php
    $statusClass = fn (\App\Enums\RunStatus $s) => match ($s) {
        \App\Enums\RunStatus::Finished => 'selesai',
        \App\Enums\RunStatus::Running => 'sedang-berjalan',
        \App\Enums\RunStatus::Called, \App\Enums\RunStatus::Ready => 'berikutnya',
        \App\Enums\RunStatus::Scheduled, \App\Enums\RunStatus::ReRun => 'terjadwal',
        default => 'sementara',
    };
@endphp

@section('content')
<div class="app lavender-page">
    <x-roborace.header :page="$page" />

    <x-roborace.page-hero
        title="Hari ini, giliran siapa?"
        :description="($event?->starts_at?->translatedFormat('l, d F Y') ?? 'Jadwal pertandingan') . ' · Semua waktu dalam WIB'"
        mascot="17d7a.png" />

    <main class="wide-main">
        <x-roborace.level-chips :active="$activeLevel" :route="'public.schedule'" :levels="$levels ?? collect(\App\Enums\Level::cases())" />

        <section class="stats-grid schedule-stats">
            <article class="card">
                <small>SESI HARI INI</small>
                <strong>{{ $todayRuns }}</strong>
                <p>Dalam kalender pertandingan</p>
            </article>
            <article class="card">
                <small>ARENA AKTIF</small>
                <strong>{{ str_pad($activeArenas, 2, '0', STR_PAD_LEFT) }}</strong>
                <p>{{ $event?->arenas->pluck('code')->take(3)->implode(' · ') ?: 'Belum ada arena' }}</p>
            </article>
            <article class="card">
                <small>SESI BERIKUTNYA</small>
                <strong>{{ $next?->scheduled_at?->format('H:i') ?? '—' }}</strong>
                <p>{{ $next?->team?->name ?? 'Belum terjadwal' }}{{ $next?->arena ? ' · '.$next->arena->name : '' }}</p>
            </article>
        </section>

        <article class="card schedule-card reveal">
            <small>JAM · TIM · ROBOT · STATUS</small>
            <h2>Jadwal arena</h2>
            @forelse ($runs as $i => $run)
                <div class="schedule-row reveal" style="animation-delay:{{ $i * 80 }}ms">
                    <div>
                        <strong>
                            {{ $run->scheduled_at?->format('H:i') ?? '—' }} · {{ $run->arena?->name }} · {{ $run->team?->name }}
                        </strong>
                        <p>
                            {{ $run->team?->school?->name }} · {{ $run->category?->level->publicLabel() }}
                            · {{ $run->team?->short_name ?? '—' }} · Sesi penyisihan
                        </p>
                    </div>
                    <span class="status {{ $statusClass($run->status) }}">{{ $run->status->label() }}</span>
                </div>
            @empty
                <p class="muted">Belum ada jadwal untuk jenjang ini.</p>
            @endforelse
        </article>

        <div class="notice">
            Jadwal dapat berubah jika ada gangguan arena. Pantau informasi dari operator sebelum check-in.
        </div>
    </main>

    <x-roborace.footer />
</div>
@endsection
