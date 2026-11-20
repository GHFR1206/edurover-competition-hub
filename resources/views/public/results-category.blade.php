@extends('layouts.roborace-public')

@section('title', 'RoboRace · Klasemen ' . $category->level->publicLabel())

@section('content')
<div class="app results-page">
    <x-roborace.header :page="$page" />

    <main class="results-main">
        <img class="sunburst" src="{{ asset('assets/4a0a9.svg') }}" alt="">
        <section class="results-title">
            <x-roborace.live-badge />
            <h1>Klasemen {{ $category->level->publicLabel() }}</h1>
            <p>{{ $category->name }} · {{ $category->level->challengeName() }}</p>
            <x-roborace.mascot src="786e4.png" />
        </section>

        @if ($podium->isNotEmpty())
            <div class="podium">
                <div class="podium-place second">
                    <b>2</b>
                    <strong>{{ $podium[1]['team']->name ?? '—' }}</strong>
                    <span>{{ isset($podium[1]) ? $podium[1]['best_total'].' poin' : 'Menunggu' }}</span>
                </div>
                <div class="podium-place first">
                    <small>🏆 JUARA 1</small>
                    <b>1</b>
                    <strong>{{ $podium[0]['team']->name }}</strong>
                    <span>{{ $podium[0]['best_total'] }} poin</span>
                </div>
                <div class="podium-place third">
                    <b>3</b>
                    <strong>{{ $podium[2]['team']->name ?? '—' }}</strong>
                    <span>{{ isset($podium[2]) ? $podium[2]['best_total'].' poin' : 'Menunggu hasil valid' }}</span>
                </div>
            </div>
        @endif

        <div class="result-filter" style="margin-top:24px">
            <span>✓ {{ $category->level->publicLabel() }} · DIPUBLIKASIKAN {{ $category->publication?->published_at?->format('d M Y') }}</span>
        </div>

        <article class="card results-table reveal">
            <div class="table-head">
                <span>Rank</span>
                <span>Tim · Sekolah</span>
                <span>Total skor</span>
                <span>Waktu</span>
                <span></span>
            </div>
            @forelse ($standings as $row)
                <div class="result-row">
                    <b>{{ $row['rank'] }}</b>
                    <div>
                        <strong>{{ $row['team']->name }}</strong>
                        <small>{{ $row['team']->school?->name }} · {{ $row['team']->short_name ?? '—' }}</small>
                    </div>
                    <strong>{{ $row['best_total'] }}</strong>
                    <span>{{ $row['runs'] }} run</span>
                    <span></span>
                </div>
            @empty
                <div class="result-row"><div><strong>Belum ada hasil</strong></div></div>
            @endforelse
            <small class="table-note">
                Skor dihitung dari skor yang telah disetujui Chief Judge. Kategori sudah dipublikasikan secara resmi.
            </small>
        </article>

        <a class="outline-btn" style="margin-top:20px" href="{{ route('public.results') }}">← Kembali ke Hasil</a>
    </main>

    <x-roborace.footer />
</div>
@endsection
