@extends('layouts.roborace-public')

@section('title', 'RoboRace · Leaderboard')

@section('content')
<div class="app leaderboard-page">
    <x-roborace.header :page="$page" :dark="true" />

    <main class="leader-main">
        <x-roborace.page-hero
            title="Leaderboard"
            :description="($event?->name ?? 'Kompetisi Robotika') . ' · Skor per level, transparan di setiap langkah.'"
            mascot="786e4.png" />

        <article class="card leader-message reveal">
            <span class="yellow-pill">✦ PAPAN PERINGKAT LANGSUNG</span>
            <h2>Setiap checkpoint, satu langkah lebih jauh.</h2>
            <p>
                ✓ Resmi setelah Chief Judge memverifikasi penilaian. Skor lainnya tetap ◷ Sementara.
            </p>
            <div class="planet">◯</div>
        </article>

        <x-roborace.level-chips :active="$activeLevel" :route="'public.leaderboard'" :dark="true" />

        @forelse ($standings as $i => $row)
            <a class="leader-card reveal {{ $i === 0 ? 'winner' : '' }}"
               style="animation-delay:{{ $i * 100 }}ms"
               href="{{ route('public.leaderboard', ['level' => $activeLevel->value]) }}">
                <span class="rank-shield">{{ $row['rank'] }}</span>
                <div>
                    <h3>
                        {{ $row['team']->name }}
                        <small>{{ $row['team']->short_name ?? '—' }}</small>
                    </h3>
                    <p>{{ $row['team']->school?->name }}</p>
                    <small>
                        {{ $row['runs'] }} run · Poin {{ $row['approved'] ? 'tersedia' : 'belum tersedia' }}
                    </small>
                </div>
                <span class="status {{ $published ? 'resmi' : 'sementara' }}">
                    {{ $published ? '✓ Resmi' : '◷ Sementara' }}
                </span>
                <strong>
                    <x-roborace.result-value :row="$row" :unit="false" />
                    <small>{{ ($row['mode'] ?? 'jury') === 'time' ? 'WAKTU' : 'POIN' }}</small>
                </strong>
            </a>
        @empty
            <p class="muted" style="color:#c9b8f0;padding:24px 0">Belum ada tim pada jenjang ini.</p>
        @endforelse
    </main>

    <x-roborace.footer />
</div>
@endsection
