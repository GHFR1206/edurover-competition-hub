@extends('layouts.roborace-event-admin')

@section('title', 'Event & Ruleset')
@section('hero-title', 'Rancang arena yang hebat.')
@section('hero-subtitle', 'Kelola event dan tahap kompetisi, dari ide sampai garis finis.')

@section('content')
@php
    $statusLabel = [
        'draft' => 'Draft',
        'scheduled' => 'Terjadwal',
        'running' => 'Berjalan',
        'finished' => 'Selesai',
        'archived' => 'Diarsipkan',
    ];
    $nextStep = ['draft' => 'Terjadwal', 'scheduled' => 'Berjalan', 'running' => 'Selesai'];
@endphp
@php($page = 'events')

<section class="card">
    <h2>Buat event</h2>
    <form method="POST" action="{{ route('eventadmin.events.store') }}">
        @csrf
        <div class="form-grid two">
            <label class="field"><span>Nama event</span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="RoboRace Festival Pelajar" required>
            </label>
            <label class="field"><span>Lokasi</span>
                <input type="text" name="venue" value="{{ old('venue') }}" placeholder="GOR Edukasi Nusantara">
            </label>
            <label class="field"><span>Tanggal mulai</span>
                <input type="date" name="starts_at" value="{{ old('starts_at') }}" required>
            </label>
            <label class="field"><span>Tanggal selesai</span>
                <input type="date" name="ends_at" value="{{ old('ends_at') }}" required>
            </label>
        </div>
        <button type="submit" class="button yellow">Buat event</button>
    </form>
</section>

<section class="card">
    <span class="eyebrow">TRANSISI SATU ARAH</span>
    <h2>Daftar Event</h2>
    <p class="muted">Setiap tahap maju satu arah. Periksa setup dan penugasan sebelum melanjutkan.</p>
    <div class="rows">
        @forelse ($events as $event)
            @php($setupComplete = $event->categories_count > 0)
            <div class="event-entry">
                <div class="row">
                    <div class="row-copy">
                        <strong>{{ $event->name }}</strong>
                        <span>
                            {{ $event->starts_at?->translatedFormat('d M Y') }}–{{ $event->ends_at?->translatedFormat('d M Y') }}
                            · {{ $event->venue ?? 'Lokasi belum diisi' }}
                            · {{ $event->categories_count }} kategori
                        </span>
                    </div>
                    <div class="row-actions">
                        <span class="badge badge-{{ str_replace(' ', '-', strtolower($statusLabel[$event->status] ?? $event->status)) }}">
                            {{ $statusLabel[$event->status] ?? $event->status }}
                        </span>
                        @if ($event->status === 'finished')
                            <a class="button" href="{{ route('public.archive') }}">Lihat arsip</a>
                        @else
                            <a class="button" href="{{ route('eventadmin.setup.index') }}">Atur setup</a>
                        @endif
                    </div>
                </div>
                @isset($nextStep[$event->status])
                    <form method="POST" action="{{ route('eventadmin.events.advance', $event) }}">
                        @csrf
                        @method('PATCH')
                        <button class="button" type="submit">Maju ke {{ $nextStep[$event->status] }}</button>
                    </form>
                @endisset
            </div>
        @empty
            <p class="muted">Belum ada event. Buat event pertama di atas.</p>
        @endforelse
    </div>
</section>
@endsection
