@extends('layouts.roborace-public')

@section('title', 'RoboRace · Event')

@section('content')
<div class="app lavender-page">
    <x-roborace.header :page="$page" />

    <x-roborace.page-hero
        title="Arena untuk ide besarmu"
        description="Temukan tantangan yang pas untuk tim sekolahmu."
        mascot="dc500.png" />

    <main class="wide-main">
        <div class="event-grid">
            @forelse ($categories as $category)
                <article class="card reveal">
                    <small>{{ strtoupper($category->level->publicLabel()) }} · {{ strtoupper($category->persona ?? '') }}</small>
                    <h3>{{ $category->level->challengeName() }}</h3>
                    <span class="yellow-pill">{{ $category->status->label() }}</span>
                    <p>{{ $category->description }}</p>
                    <p class="muted">{{ $teamCounts->get($category->id, 0) }} tim terdaftar</p>
                    <a class="purple-btn" href="{{ route('public.schedule', ['level' => $category->level->value]) }}">Lihat jadwal</a>
                </article>
            @empty
                <article class="card reveal">
                    <h3>Belum ada event</h3>
                    <p class="muted">Panitia belum mempublikasikan event.</p>
                </article>
            @endforelse
        </div>

        <article class="card levels-card reveal">
            <h2>Tiga level. Tiga petualangan.</h2>
            @foreach ($categories as $category)
                <div class="level-line">
                    <div>
                        <strong>{{ $category->level->publicLabel() }} · {{ $category->level->challengeName() }}</strong>
                        <p>{{ $category->description }}</p>
                    </div>
                    <b>{{ $teamCounts->get($category->id, 0) }}</b>
                </div>
            @endforeach
        </article>

        <div class="mascot-note">
            <x-roborace.mascot src="17d7a.png" />
            Pilih level sesuai jenjang sekolah. Tim terverifikasi akan mendapat robot_code dan API key.
        </div>
    </main>

    <x-roborace.footer />
</div>
@endsection
