@extends('layouts.roborace-chief')

@section('title', 'Banding')

@php($page = 'appeals')

@section('hero')@endsection
@section('hero-title', 'Tinjau banding dengan adil.')
@section('hero-subtitle', 'Periksa alasan banding dari coach, lalu putuskan dengan catatan yang jelas.')
@section('hero-mascot', 'd8b40.png')

@section('content')

@forelse ($appeals as $appeal)
    <section class="card">
        <span class="eyebrow purple">{{ strtoupper($appeal->run?->category?->level->publicLabel() ?? '') }} · {{ $appeal->run?->arena?->name }}</span>
        <h2>{{ $appeal->team?->name }} · {{ $appeal->team?->school?->name }}</h2>
        <div class="badge {{ $appeal->status === 'accepted' ? 'green' : ($appeal->status === 'rejected' ? 'yellow' : '') }}">
            {{ ucfirst(str_replace('_', ' ', $appeal->status)) }}
        </div>

        <div class="rows">
            <div class="data-row">
                <div>
                    <strong>Alasan banding</strong>
                    <span>{{ $appeal->reason }}</span>
                </div>
            </div>
            @if ($appeal->resolution)
                <div class="data-row">
                    <div>
                        <strong>Keputusan</strong>
                        <span>{{ $appeal->resolution }} · {{ $appeal->resolved_at?->translatedFormat('d M Y H:i') }} WIB</span>
                    </div>
                </div>
            @endif
        </div>

        @if (in_array($appeal->status, ['submitted', 'under_review'], true))
            <form method="POST" action="{{ route('chief.appeals.resolve', $appeal) }}">
                @csrf
                @method('PATCH')
                <label for="status-{{ $appeal->id }}">Status</label>
                <select id="status-{{ $appeal->id }}" name="status">
                    <option value="under_review" @selected($appeal->status === 'under_review')>Sedang ditinjau</option>
                    <option value="accepted">Diterima</option>
                    <option value="rejected">Ditolak</option>
                </select>

                <label for="resolution-{{ $appeal->id }}">Catatan keputusan *</label>
                <textarea id="resolution-{{ $appeal->id }}" name="resolution" required maxlength="2000" placeholder="Jelaskan dasar keputusan..."></textarea>

                <div class="actions">
                    <button class="button primary" type="submit">Simpan keputusan</button>
                </div>
            </form>
        @endif
    </section>
@empty
    <section class="card">
        <p class="muted">Belum ada banding dari coach. Berita baik!</p>
    </section>
@endforelse
@endsection
