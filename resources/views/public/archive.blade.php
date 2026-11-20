@extends('layouts.roborace-public')

@section('title', 'RoboRace · Arsip Hasil')

@section('content')
<div class="app archive-page">
    <x-roborace.header :page="$page" />

    <x-roborace.page-hero
        title="Jejak juara, dari tahun ke tahun"
        description="Buka kembali pencapaian hebat dan hasil resmi setiap musim."
        mascot="e638f.png" />

    <main class="wide-main archive-main">
        <div class="chips year-chips">
            @foreach ($years as $year)
                <a href="{{ route('public.archive', ['year' => $year]) }}"
                   class="{{ $activeYear === $year ? 'active' : '' }}">{{ $year }}</a>
            @endforeach
        </div>

        @forelse ($archive as $i => $entry)
            <article class="card reveal archive-card" style="animation-delay:{{ $i * 100 }}ms">
                <small>{{ $entry['event']->starts_at?->translatedFormat('d F Y') }} · SELESAI</small>
                <h2>{{ $entry['event']->name }}</h2>
                <span class="status resmi">✓ Arsip resmi</span>
                <p class="muted">{{ $entry['event']->venue }}</p>

                @foreach ($entry['champions'] as $champion)
                    @php($top = $champion['rows']->first())
                    @if ($top)
                        <div class="archive-row">
                            <div>
                                <strong>🏅 {{ $champion['category']->level->publicLabel() }} · {{ $top['team']->name }}</strong>
                                <small>Juara 1 · {{ $top['team']->school?->name }}</small>
                            </div>
                            <b>{{ $top['best_total'] }}</b>
                            <span class="status resmi">✓ Resmi</span>
                        </div>
                    @endif
                @endforeach

                <div class="archive-actions">
                    <a class="outline-btn" href="{{ route('public.results') }}">Lihat hasil terkini</a>
                </div>
            </article>
        @empty
            <article class="card reveal">
                <h2>Belum ada arsip {{ $activeYear }}</h2>
                <p class="muted">Musim ini belum ditutup atau belum dipublikasikan.</p>
            </article>
        @endforelse

        <div class="mascot-note">
            <x-roborace.mascot src="e638f.png" />
            Setiap musim punya cerita. Hasil arsip tidak berubah tanpa koreksi resmi dengan alasan yang tercatat.
        </div>
    </main>

    <x-roborace.footer />
</div>
@endsection
