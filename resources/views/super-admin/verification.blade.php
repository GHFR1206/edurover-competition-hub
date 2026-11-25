@extends('layouts.roborace-super-admin')

@section('title', 'Verifikasi Tim')
@php($page = 'verifikasi')

@section('content')
<div class="content decorated">
    <div class="chips">
        <a class="button {{ ! $activeStatus ? 'primary' : '' }}" href="{{ route('superadmin.verification.index') }}">Semua · {{ $counts['all'] }}</a>
        <a class="button {{ $activeStatus === 'submitted' ? 'primary' : '' }}" href="{{ route('superadmin.verification.index', ['status' => 'submitted']) }}">Menunggu · {{ $counts['submitted'] }}</a>
        <a class="button {{ $activeStatus === 'under_review' ? 'primary' : '' }}" href="{{ route('superadmin.verification.index', ['status' => 'under_review']) }}">Ditinjau · {{ $counts['under_review'] }}</a>
        <a class="button {{ $activeStatus === 'revision' ? 'primary' : '' }}" href="{{ route('superadmin.verification.index', ['status' => 'revision']) }}">Perlu Revisi · {{ $counts['revision'] }}</a>
        <a class="button {{ $activeStatus === 'verified' ? 'primary' : '' }}" href="{{ route('superadmin.verification.index', ['status' => 'verified']) }}">Terverifikasi · {{ $counts['verified'] }}</a>
        <a class="button {{ $activeStatus === 'rejected' ? 'primary' : '' }}" href="{{ route('superadmin.verification.index', ['status' => 'rejected']) }}">Ditolak · {{ $counts['rejected'] }}</a>
    </div>

    <section class="card">
        <span class="eyebrow">ANTREAN VERIFIKASI TIM</span>
        <h2>Pengajuan masuk</h2>
        <div class="rows">
            @forelse ($registrations as $registration)
                <div class="data-row">
                    <div>
                        <strong>{{ $registration->team?->name ?? 'Tim' }} · {{ $registration->team?->school?->name ?? 'Sekolah' }}</strong>
                        <span>
                            Kategori {{ $registration->category?->level->publicLabel() }} ·
                            Coach {{ $registration->coach?->name ?? '—' }} ·
                            {{ $registration->submitted_at?->translatedFormat('d M Y, H:i') ?? 'Belum diajukan' }} WIB
                        </span>
                    </div>
                    <div class="row-actions">
                        <span class="status {{ $registration->status->value === 'verified' ? 'green' : (in_array($registration->status->value, ['rejected', 'revision'], true) ? 'red' : 'yellow') }}">
                            {{ $registration->status->label() }}
                        </span>
                        <a class="button" href="{{ route('superadmin.verification.show', $registration) }}">Periksa</a>
                    </div>
                </div>
            @empty
                <p class="empty">Belum ada tim pada daftar ini.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
