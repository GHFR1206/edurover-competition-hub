@extends('layouts.roborace-chief')

@section('title', 'Tinjau Skor')

@php($page = 'review')

@section('hero')@endsection
@section('hero-title', 'Tinjau skor ' . ($score->run?->team?->name ?? ''))
@section('hero-subtitle', ($score->run?->arena?->name ?? 'Arena') . ' · Juri ' . ($score->judge?->name ?? '—') . ' · ' . $score->status->label())
@section('hero-mascot', 'd8b40.png')

@section('content')
<div class="actions">
    <a class="button" href="{{ route('chief.verification.index') }}">← Kembali ke antrean</a>
    <span class="badge {{ $score->status->value === 'approved' ? 'green' : 'yellow' }}">{{ $score->status->label() }}</span>
</div>

<section class="card">
    <span class="eyebrow">RINGKASAN PENILAIAN</span>
    <h2>{{ $score->run?->team?->name }} · {{ $score->run?->category?->level->publicLabel() }}</h2>
    <div class="stats">
        <div class="card">
            <span class="eyebrow">SUBTOTAL</span>
            <b>{{ $score->subtotal }}</b>
            <p>Poin sebelum penalti</p>
        </div>
        <div class="card">
            <span class="eyebrow">PENALTI</span>
            <b>{{ $score->penalty_total }}</b>
            <p>Pengurang total</p>
        </div>
        <div class="card">
            <span class="eyebrow">TOTAL</span>
            <b>{{ $score->total }}</b>
            <p>subtotal − penalti</p>
        </div>
    </div>
</section>

<section class="card">
    <span class="eyebrow">RINCIAN BARIS SKOR</span>
    <h2>Detail penilaian</h2>
    <div class="rows">
        @forelse ($score->lines as $line)
            <div class="data-row">
                <div>
                    <strong>{{ $line->scoreObject?->name }}</strong>
                    <span>
                        {{ $line->points_per_unit_snapshot }} poin/unit · jumlah {{ $line->quantity }}
                        @if ($line->achieved) · ✓ tercapai @endif
                        @if ($line->notes) · {{ $line->notes }} @endif
                    </span>
                </div>
                <div class="score">{{ $line->computed_points }}</div>
            </div>
        @empty
            <p class="muted">Tidak ada baris skor.</p>
        @endforelse
    </div>
</section>

@if ($score->approvals->isNotEmpty())
    <section class="card">
        <span class="eyebrow">JEJAK KEPUTUSAN</span>
        <h2>Riwayat keputusan</h2>
        <div class="rows">
            @foreach ($score->approvals as $approval)
                <div class="data-row">
                    <div>
                        <strong>{{ ucfirst($approval->decision) }} · {{ $approval->decidedBy?->name ?? 'Sistem' }}</strong>
                        <span>{{ $approval->decided_at?->translatedFormat('d M Y H:i') }} WIB @if ($approval->reason) · {{ $approval->reason }} @endif</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<div class="correction-grid">
    <section class="card decision-card">
        <span class="eyebrow purple">SETUJUI</span>
        <h2>Jadikan resmi</h2>
        <p class="decision-description">Skor menjadi resmi. Hanya dapat dilakukan bila Anda bukan penulisnya.</p>
        <form method="POST" action="{{ route('chief.verification.approve', $score) }}">
            @csrf
            @method('PATCH')
            <div class="actions">
                <button class="button primary" type="submit" @disabled($score->judge_id === auth()->id())>Setujui skor</button>
            </div>
        </form>
        @if ($score->judge_id === auth()->id())
            <small>Maker-checker: skor ini Anda tulis sendiri, tombol dinonaktifkan.</small>
        @endif
    </section>

    <section class="card decision-card">
        <span class="eyebrow purple">TOLAK / BUKA ULANG</span>
        <h2>Kembalikan ke juri</h2>
        <form method="POST" action="{{ route('chief.verification.reject', $score) }}">
            @csrf
            @method('PATCH')
            <label for="reject-reason">Alasan penolakan *</label>
            <input id="reject-reason" type="text" name="reason" required maxlength="255" placeholder="Nilai manual belum dilengkapi">
            <div class="actions">
                <button class="button danger" type="submit">Tolak skor</button>
            </div>
        </form>
        <form method="POST" action="{{ route('chief.verification.reopen', $score) }}">
            @csrf
            @method('PATCH')
            <label for="reopen-reason">Alasan pembukaan ulang *</label>
            <input id="reopen-reason" type="text" name="reason" required maxlength="255" placeholder="Bukti tambahan ditemukan">
            <div class="actions">
                <button class="button" type="submit">Buka ulang</button>
            </div>
        </form>
    </section>
</div>
@endsection
