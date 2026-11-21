@extends('layouts.roborace-chief')

@section('title', 'Publikasi Hasil')

@php($page = 'review')

@section('hero')@endsection
@section('hero-title', 'Saatnya hasil resmi.')
@section('hero-subtitle', 'Publikasi terpisah dari persetujuan. Kategori hanya terbit bila semua run selesai dan semua skor disetujui.')
@section('hero-mascot', 'd8b40.png')

@section('content')
<div class="warning">
    Publikasi <strong>terpisah</strong> dari persetujuan. Alasan penghalang selalu ditampilkan di bawah setiap kategori.
</div>

<section class="card">
    <form method="GET">
        <label for="event">Event</label>
        <select id="event" name="event" onchange="this.form.submit()">
            @foreach ($events as $option)
                <option value="{{ $option->id }}" @selected($event && $event->id === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
        <div class="actions">
            <button class="button primary" type="submit">Tampilkan</button>
        </div>
    </form>
</section>

@if (! $event)
    <p class="muted">Belum ada event.</p>
@endif

@foreach ($categories as $row)
    @php($category = $row['category'])
    <section class="card level-card">
        <span class="eyebrow purple">{{ $category->level->publicLabel() }} · {{ $category->name }}</span>
        <h2>{{ $category->level->publicLabel() }} — {{ $category->name }}</h2>
        <p class="muted">Status kategori: {{ $category->status->label() }}</p>

        @if ($category->isPublished())
            <span class="badge green">✓ Dipublikasikan</span>
            <p class="publish-meta">Hasil sudah resmi. Jejak pengesahan tersimpan di audit log.</p>
            <div class="actions">
                <a class="button primary" href="{{ route('public.results.category', $category) }}">Buka hasil resmi</a>
                <form method="POST" action="{{ route('chief.publication.certificates', $category) }}">
                    @csrf
                    <button class="button yellow" type="submit">Terbitkan sertifikat</button>
                </form>
            </div>
        @elseif ($row['publishable'])
            <span class="badge yellow">Siap dipublikasikan</span>
            <p class="publish-meta">Semua run selesai dan semua skor telah disetujui.</p>
            <form method="POST" action="{{ route('chief.publication.publish', $category) }}">
                @csrf
                <div class="actions">
                    <button class="button yellow" type="submit">Sahkan &amp; publikasikan</button>
                </div>
            </form>
        @else
            <span class="badge">Belum dapat dipublikasikan</span>
            @if (! empty($row['reasons']))
                <div class="warning">
                    <ul>
                        @foreach ($row['reasons'] as $reason)
                            <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        @if ($row['standings']->isNotEmpty())
            <details>
                <summary>Pratinjau klasemen</summary>
                <div class="rows">
                    @foreach ($row['standings'] as $standing)
                        <div class="data-row">
                            <span class="rank {{ $standing['rank'] === 1 ? 'rank-1' : '' }}">{{ $standing['rank'] }}</span>
                            <div>
                                <strong>{{ $standing['team']->name }}</strong>
                                <span>{{ $standing['team']->school?->name }} · {{ $standing['runs'] }} run</span>
                            </div>
                            <div class="score"><x-roborace.result-value :row="$standing" :unit="false" /></div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </section>
@endforeach

@if ($event && $categories->isEmpty())
    <p class="muted">Event ini belum memiliki kategori.</p>
@endif
@endsection
