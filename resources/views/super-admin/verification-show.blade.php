@extends('layouts.roborace-super-admin')

@section('title', 'Verifikasi · ' . ($registration->team?->name ?? 'Tim'))
@php($page = 'verifikasi')

@section('content')
<div class="content decorated">
    <a class="button" href="{{ route('superadmin.verification.index') }}">← Kembali ke antrean</a>

    <section class="card review-card">
        <small class="eyebrow">
            PENGAJUAN {{ $registration->submitted_at?->translatedFormat('d F Y · H:i') ?? '—' }} WIB
        </small>
        <h2>{{ $registration->team?->name ?? 'Tim tanpa nama' }}</h2>
        <span class="status {{ $registration->status->value === 'verified' ? 'green' : ($registration->status->value === 'rejected' ? 'red' : 'yellow') }}">
            {{ $registration->status->label() }}
        </span>

        <div class="team-detail">
            <p>
                Level {{ $registration->category?->level->publicLabel() }} · {{ $registration->category?->name }}
                <br>
                Coach: {{ $registration->coach?->name ?? '—' }}
                <br>
                Email: {{ $registration->coach?->email ?? '—' }}
            </p>
            <p>
                {{ $registration->team?->participants?->count() ?? 0 }} anggota:
                {{ $registration->team?->participants?->pluck('name')->implode(', ') ?: '—' }}
                <br>
                Sekolah: {{ $registration->team?->school?->name ?? '—' }}
                <br>
                Persetujuan orang tua: {{ $registration->consent_parent ? '✓ Tercatat' : '— Belum' }}
            </p>
        </div>

        @if ($blockers)
            <div class="notice" style="background:#fde3e7;color:#b3283f">
                <strong>Belum dapat diverifikasi:</strong>
                <ul>
                    @foreach ($blockers as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($registration->rejection_reason)
            <div class="notice">
                {{ $registration->status === \App\Enums\RegistrationStatus::Revision ? 'Catatan revisi' : 'Catatan terakhir' }}: {{ $registration->rejection_reason }}
            </div>
        @endif

        @if ($registration->status->isAwaitingDecision())
            <div class="button-row">
                <form method="POST" action="{{ route('superadmin.verification.approve', $registration) }}">
                    @csrf
                    @method('PATCH')
                    <button class="button primary" type="submit" @disabled($blockers !== [])>Setujui</button>
                </form>
            </div>

            <form method="POST" action="{{ route('superadmin.verification.reject', $registration) }}">
                @csrf
                @method('PATCH')
                <label>Catatan penolakan / revisi *</label>
                <textarea name="reason" placeholder="Surat pendamping belum ditandatangani kepala sekolah. Mohon unggah kembali dokumen yang lengkap.">{{ old('reason') }}</textarea>
                <small>* Wajib diisi untuk tolak / revisi</small>
                <div class="button-row">
                    <button class="button danger" type="submit" formaction="{{ route('superadmin.verification.reject', $registration) }}">Tolak</button>
                    <button class="button yellow" type="submit" formaction="{{ route('superadmin.verification.revision', $registration) }}">Minta revisi</button>
                </div>
            </form>
        @else
            <div class="notice">
                Pengajuan ini berstatus <strong>{{ $registration->status->label() }}</strong> sehingga tidak ada keputusan
                yang bisa diambil lagi. Coach perlu mengajukan ulang agar masuk antrean.
            </div>
        @endif
    </section>

    <section class="card">
        <h2>Riwayat run tim</h2>
        <div class="rows">
            @forelse ($registration->team?->runs ?? [] as $run)
                <div class="data-row">
                    <div>
                        <strong>{{ $run->arena?->name }} · {{ $run->category?->level->publicLabel() }}</strong>
                        <span>{{ $run->scheduled_at?->translatedFormat('d M Y, H:i') ?? '—' }} WIB</span>
                    </div>
                    <span class="status">{{ $run->status->label() }}</span>
                </div>
            @empty
                <p class="empty">Belum ada run untuk tim ini.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
