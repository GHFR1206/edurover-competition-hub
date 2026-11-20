@extends('layouts.roborace-super-admin')

@section('title', 'Ringkasan')
@php($page = 'ringkasan')

@section('hero-actions')
    <a class="button primary" href="{{ route('superadmin.users.index') }}">Kelola pengguna</a>
    <a class="button yellow" href="{{ route('superadmin.audit.index') }}">Buka audit log</a>
@endsection
@section('hero-note')
    {{ now()->translatedFormat('l, d F Y') }} · Pembaruan {{ now()->format('H:i') }} WIB
@endsection

@section('content')
<div class="content summary-content">
    <div class="stats">
        <section class="card stat" style="--delay:0ms">
            <small>PENGGUNA TERDAFTAR</small>
            <strong>{{ $userCount }}</strong>
            <span>Coach, panitia &amp; juri</span>
        </section>
        <section class="card stat" style="--delay:80ms">
            <small>TIM TERDAFTAR</small>
            <strong>{{ $teamCount }}</strong>
            <span>Seluruh jenjang</span>
        </section>
        <section class="card stat" style="--delay:160ms">
            <small>EVENT</small>
            <strong>{{ str_pad($eventCount, 2, '0', STR_PAD_LEFT) }}</strong>
            <span>Terdaftar di platform</span>
        </section>
        <section class="card stat" style="--delay:240ms">
            <small>PERLU VERIFIKASI</small>
            <strong>{{ str_pad($pendingCount, 2, '0', STR_PAD_LEFT) }}</strong>
            <span>Bantu tim masuk arena</span>
        </section>
    </div>

    <div class="monitor-grid">
        <section class="card">
            <h2>Monitoring Event</h2>
            @forelse ($events as $event)
                <div class="data-row">
                    <div>
                        <strong>{{ $event->name }}</strong>
                        <span>{{ $event->starts_at?->translatedFormat('d–M Y') }} · {{ $event->categories_count }} kategori · {{ $event->arenas_count }} arena</span>
                    </div>
                    <span class="status {{ $event->status === 'running' ? 'green' : ($event->status === 'scheduled' ? 'yellow' : '') }}">{{ ucfirst($event->status) }}</span>
                </div>
            @empty
                <p class="empty">Belum ada event.</p>
            @endforelse
        </section>
        <section class="card">
            <small class="eyebrow">SEMUA SISTEM DALAM PANTAUAN</small>
            <h2>Monitoring Sistem</h2>
            @foreach ($system as $row)
                <div class="data-row">
                    <div>
                        <strong>{{ $row['label'] }}</strong>
                        <span>{{ $row['value'] }}</span>
                    </div>
                    <span class="status {{ $row['tone'] === 'ok' ? 'green' : 'yellow' }}">● Normal</span>
                </div>
            @endforeach
        </section>
    </div>

    <section class="card">
        <h2>Audit Terbaru</h2>
        @forelse ($recentAudit as $log)
            <div class="data-row">
                <div>
                    <strong>{{ ucfirst($log->action) }} · {{ class_basename($log->auditable_type ?? 'Sistem') }}</strong>
                    <span>{{ $log->actor_name ?? 'Sistem' }} · {{ $log->created_at?->translatedFormat('d M, H:i') }} WIB</span>
                </div>
                <span class="status">{{ ucfirst($log->action) }}</span>
            </div>
        @empty
            <p class="empty">Belum ada aktivitas tercatat.</p>
        @endforelse
    </section>
</div>
@endsection
