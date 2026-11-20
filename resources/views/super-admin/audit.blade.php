@extends('layouts.roborace-super-admin')

@section('title', 'Audit Log')
@php($page = 'audit')

@section('content')
<div class="content decorated">
    <div class="chips">
        @foreach ($categories as $category)
            <a class="button {{ $activeCategory === $category ? 'primary' : '' }}"
               href="{{ route('superadmin.audit.index', array_filter(['kategori' => $category === 'Semua' ? null : $category, 'q' => $search])) }}">
                {{ $category }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('superadmin.audit.index') }}" class="search-line two">
        @if ($activeCategory !== 'Semua')<input type="hidden" name="kategori" value="{{ $activeCategory }}">@endif
        <label>Cari aktivitas
            <input type="text" name="q" value="{{ $search }}" placeholder="Nama, tim, event, atau tindakan...">
        </label>
        <label>Rentang waktu
            <input value="{{ now()->translatedFormat('d F Y') }}" readOnly>
        </label>
        <button class="button" type="submit">Cari</button>
    </form>

    <section class="card">
        <small class="eyebrow">{{ strtoupper(now()->translatedFormat('d F Y')) }} · WIB</small>
        <h2>Riwayat aktivitas</h2>
        <div class="table">
            <div class="table-head">
                <span>AKSI</span>
                <span>PELAKU</span>
                <span>OBJEK</span>
                <span>PERUBAHAN</span>
                <span>WAKTU</span>
            </div>
            @forelse ($logs as $log)
                <div class="table-row">
                    <strong>{{ ucfirst($log->action) }}</strong>
                    <span>{{ $log->actor_name ?? 'Sistem' }}</span>
                    <span>{{ class_basename($log->auditable_type ?? '—') }} #{{ $log->auditable_id ?? '—' }}</span>
                    <span>{{ $log->reason ?? '—' }}</span>
                    <b>{{ $log->created_at?->format('H:i') ?? '—' }}</b>
                </div>
            @empty
                <p class="empty">Belum ada aktivitas tercatat.</p>
            @endforelse
        </div>
        <div class="pagination">
            <span class="status">{{ $logs->count() }} dari {{ $logs->total() }} aktivitas</span>
            @if ($logs->onFirstPage())
                <button class="button" disabled>Sebelumnya</button>
            @else
                <a class="button" href="{{ $logs->previousPageUrl() }}">Sebelumnya</a>
            @endif
            @if ($logs->hasMorePages())
                <a class="button" href="{{ $logs->nextPageUrl() }}">Berikutnya</a>
            @else
                <button class="button" disabled>Berikutnya</button>
            @endif
        </div>
    </section>

    <p class="audit-note">
        Riwayat tidak dapat dihapus dari antarmuka. Koreksi menyimpan nilai lama, nilai baru, serta alasan.
    </p>
</div>
@endsection
