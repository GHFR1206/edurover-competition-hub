@extends('layouts.roborace-public')

@section('title', 'RoboRace · Hasil Resmi')

@section('content')
<div class="app results-page">
    <x-roborace.header :page="$page" />

    <main class="results-main">
        <img class="sunburst" src="{{ asset('assets/4a0a9.svg') }}" alt="">
        <section class="results-title">
            <x-roborace.live-badge />
            <h1>Hasil Resmi</h1>
            <p>{{ $category?->level->challengeName() ?? ($event?->name ?? 'Kompetisi Robotika') }}</p>
            <x-roborace.mascot src="786e4.png" />
        </section>

        <x-roborace.level-chips :active="$activeLevel" :route="'public.results'" />

        @if ($published && $podium->isNotEmpty())
            <div class="podium">
                <div class="podium-place second">
                    <b>2</b>
                    <strong>{{ $podium[1]['team']->name ?? '—' }}</strong>
                    <span>{{ isset($podium[1]) ? '' : 'Menunggu' }}@isset($podium[1])<x-roborace.result-value :row="$podium[1]" />@endisset</span>
                </div>
                <div class="podium-place first">
                    <small>🏆 JUARA 1</small>
                    <b>1</b>
                    <strong>{{ $podium[0]['team']->name }}</strong>
                    <span><x-roborace.result-value :row="$podium[0]" /></span>
                </div>
                <div class="podium-place third">
                    <b>3</b>
                    <strong>{{ $podium[2]['team']->name ?? '—' }}</strong>
                    <span>{{ isset($podium[2]) ? '' : 'Menunggu hasil valid' }}@isset($podium[2])<x-roborace.result-value :row="$podium[2]" />@endisset</span>
                </div>
            </div>
        @else
            <div class="notice">
                ◷ Hasil {{ $activeLevel->publicLabel() }} belum dipublikasikan — juri masih menuntaskan penilaian.
            </div>
        @endif

        <div class="dash"></div>

        <div class="result-filter">
            <span>{{ $published ? '✓' : '◷' }} {{ $activeLevel->publicLabel() }} · {{ $published ? 'DIPUBLIKASIKAN' : 'BELUM DIPUBLIKASIKAN' }}</span>
        </div>

        <article class="card results-table reveal">
            <div class="table-head">
                <span>Rank</span>
                <span>Tim · Sekolah</span>
                <span>{{ $isTimeMode ? 'Waktu resmi' : 'Total skor' }}</span>
                <span>{{ $isTimeMode ? 'Lap' : 'Waktu' }}</span>
                <span></span>
            </div>
            @forelse ($standings as $row)
                <div class="result-row">
                    <b>{{ $row['rank'] }}</b>
                    <div>
                        <strong>{{ $row['team']->name }}</strong>
                        <small>{{ $row['team']->school?->name }} · {{ $row['team']->short_name ?? '—' }}</small>
                    </div>
                    <strong><x-roborace.result-value :row="$row" :unit="false" /></strong>
                    @if ($isTimeMode)
                        <span>{{ $row['lap_count'] ? $row['lap_count'].' lap' : '—' }}</span>
                    @else
                        <span>{{ $row['time_ms'] !== null ? \App\Services\StandingsService::formatMs($row['time_ms']) : $row['runs'].' run' }}</span>
                    @endif
                    <span></span>
                </div>
            @empty
                <div class="result-row">
                    <div><strong>Belum ada hasil</strong><small>Hasil tampil setelah dipublikasikan.</small></div>
                </div>
            @endforelse
            <small class="table-note">
                Nilai juri bersifat aditif terhadap skor robot. Klik kategori untuk rincian checkpoint.
            </small>
        </article>

        @if ($publishedCategories->isNotEmpty())
            <article class="card reveal" style="margin-top:24px">
                <h2 style="font-size:24px">Hasil terpublikasi</h2>
                @foreach ($publishedCategories as $cat)
                    <div class="schedule-row">
                        <div>
                            <strong>{{ $cat->level->publicLabel() }} — {{ $cat->name }}</strong>
                            <p>{{ $cat->level->challengeName() }}</p>
                        </div>
                        <a class="outline-btn mini" href="{{ route('public.results.category', $cat) }}">Lihat rincian</a>
                    </div>
                @endforeach
            </article>
        @endif
    </main>

    <x-roborace.footer />
</div>
@endsection
