@extends('layouts.roborace-event-admin')

@section('title', 'Jadwal & Arena')
@php($page = 'schedule')
@section('hero-title', 'Semua punya giliran melesat.')
@section('hero-subtitle', 'Susun sesi tanpa bentrok dan jaga ritme setiap arena.')

@section('content')
<section class="card">
    <h2>Tambah sesi</h2>
    <form method="POST" action="{{ route('eventadmin.schedule.store') }}">
        @csrf
        <div class="form-grid two">
            <label class="field"><span>Tim</span>
                <select name="team_id" required>
                    <option value="">— Pilih tim —</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>
                            {{ $team->name }} · {{ $team->category?->level->publicLabel() }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="field"><span>Arena</span>
                <select name="arena_id" required>
                    <option value="">— Pilih arena —</option>
                    @foreach ($arenas as $arena)
                        <option value="{{ $arena->id }}" @selected(old('arena_id') == $arena->id)>{{ $arena->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="form-grid three">
            <label class="field"><span>Waktu mulai</span>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required>
            </label>
            <label class="field"><span>Durasi slot (menit)</span>
                <input type="number" name="duration" value="{{ old('duration', 15) }}" min="1" max="120">
            </label>
        </div>
        <button type="submit" class="button yellow">Tambah sesi</button>
    </form>
</section>

<div class="chips">
    <a class="chip {{ ! $arenaFilter ? 'active' : '' }}" href="{{ route('eventadmin.schedule.index') }}">Semua arena</a>
    @foreach ($arenas as $arena)
        <a class="chip {{ $arenaFilter === $arena->id ? 'active' : '' }}" href="{{ route('eventadmin.schedule.index', ['arena' => $arena->id]) }}">{{ $arena->name }}</a>
    @endforeach
</div>

<section class="card">
    <h2>Sesi terjadwal</h2>
    <div class="rows">
        @forelse ($runs as $run)
            <div class="row">
                <div class="row-copy">
                    <strong>{{ $run->scheduled_at?->translatedFormat('d M Y, H:i') }} WIB · {{ $run->team?->name }}</strong>
                    <span>{{ $run->arena?->name }} · {{ $run->category?->level->publicLabel() }} · {{ $run->team?->school?->name }}</span>
                </div>
                <div class="row-actions">
                    <span class="badge badge-{{ str_replace('_', '-', $run->status->value) }}">{{ $run->status->label() }}</span>
                </div>
            </div>
        @empty
            <p class="muted">Belum ada sesi terjadwal untuk event ini.</p>
        @endforelse
    </div>
</section>

<div class="notice warning">
    Jadwal tidak boleh bentrok per arena dan per tim. Sistem menolak sesi yang bertabrakan.
</div>
@endsection
