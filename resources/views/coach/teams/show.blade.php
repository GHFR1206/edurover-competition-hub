@extends('layouts.roborace-coach')

@section('title', $team->name)

@php($page = 'team')

@section('content')
<section class="hero hero-schedule">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>{{ $team->name }}</h1>
                <p>{{ $team->school?->name }} · {{ $team->category?->level->publicLabel() }}</p>
            </div>
            <img class="hero-mascot schedule-mascot" src="{{ asset('assets/17d7a.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="ornament" aria-hidden="true">
        <img src="{{ asset('assets/98d5a.svg') }}" alt="">
        <span class="gear-cut"><img src="{{ asset('assets/98d5a.svg') }}" alt=""></span>
    </div>
    <div class="content-width page-stack">
        <div class="two-column">
            <section class="card reveal">
                <span class="eyebrow">{{ strtoupper($team->school?->name ?? 'SEKOLAH') }} · {{ $team->category?->level->publicLabel() }}</span>
                <h2>{{ $team->name }}</h2>
                <span class="status-badge {{ $team->status->value === 'verified' ? 'success' : 'warning' }}">{{ $team->status->label() }}</span>
                <p class="body-dark">{{ $team->category?->event?->name ?? 'RoboRace' }} · {{ $team->short_name ?? '—' }}</p>
            </section>
            <section class="card reveal" style="animation-delay:80ms">
                <span class="eyebrow neutral">RINGKASAN RUN</span>
                <h2>{{ $team->runs->count() }} run</h2>
                <p>Total run tercatat untuk tim ini di seluruh arena.</p>
            </section>
        </div>

        <section class="card reveal" style="animation-delay:140ms">
            <span class="eyebrow">PENDAFTARAN</span>
            <h2>Status pendaftaran</h2>
            @if ($registration)
                <div class="data-list">
                    <div class="data-row">
                        <div>
                            <strong>{{ $registration->status->label() }}</strong>
                            <span>
                                Diajukan {{ $registration->submitted_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                                · Disahkan {{ $registration->verified_at?->translatedFormat('d M Y') ?? '—' }}
                            </span>
                            @if ($registration->rejection_reason)
                                <span>
                                    {{ $registration->status === \App\Enums\RegistrationStatus::Revision ? 'Perbaiki dulu' : 'Catatan panitia' }}:
                                    {{ $registration->rejection_reason }}
                                </span>
                            @endif
                        </div>
                        <span class="status-badge {{ $registration->status->value === 'verified' ? 'success' : 'warning' }}">
                            {{ $registration->status->label() }}
                        </span>
                    </div>
                </div>
            @else
                <p>Belum ada data pendaftaran untuk tim ini.</p>
            @endif

            @if ($editable)

                <form method="POST" action="{{ route('coach.teams.submit', $team) }}" style="width:100%;margin-top:16px">
                    @csrf
                    @if ($registration?->status === \App\Enums\RegistrationStatus::Revision)
                        <div class="notice warning">
                            Panitia meminta perbaikan. Ubah data tim bila perlu, lalu ajukan ulang.
                        </div>
                    @endif
                    <div class="consent-list">
                        <label>
                            <input type="checkbox" name="consent_parent" value="1" required
                                   @checked(old('consent_parent') || $registration->consent_parent)>
                            <span>Orang tua/wali setiap anggota telah memberi persetujuan tertulis untuk mengikuti kompetisi.</span>
                        </label>
                        <label>
                            <input type="checkbox" name="consent_media" value="1" required
                                   @checked(old('consent_media') || $registration->consent_media)>
                            <span>Kami mengizinkan dokumentasi foto/video tim dipublikasikan untuk keperluan kompetisi.</span>
                        </label>
                    </div>
                    <div class="actions">
                        <button class="button primary" type="submit">
                            {{ $registration?->status === \App\Enums\RegistrationStatus::Revision ? 'Ajukan ulang' : 'Ajukan verifikasi' }}
                        </button>
                    </div>
                </form>
            @endif
        </section>

        <section class="card reveal" style="animation-delay:170ms">
            <h2>Anggota tim</h2>
            <div class="data-list">
                @forelse ($team->participants as $participant)
                    <div class="data-row">
                        <div>
                            <strong>{{ $participant->name }}</strong>
                            <span>{{ $participant->role === 'leader' ? 'Ketua tim' : ($participant->role === 'reserve' ? 'Cadangan' : 'Anggota') }}
                                @if ($participant->grade) · Kelas {{ $participant->grade }} @endif
                            </span>
                        </div>
                        @if ($editable)
                            <form method="POST"
                                  action="{{ route('coach.teams.participants.destroy', [$team, $participant]) }}"
                                  onsubmit="return confirm('Hapus anggota ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="button secondary" type="submit">Hapus</button>
                            </form>
                        @else
                            <span class="status-badge success">Tercatat</span>
                        @endif
                    </div>
                @empty
                    <p>Belum ada anggota tercatat.</p>
                @endforelse
            </div>

            @if ($editable)
                <form method="POST" action="{{ route('coach.teams.participants.store', $team) }}"
                      style="width:100%;margin-top:16px">
                    @csrf
                    <h3>Tambah anggota</h3>
                    <div class="form-grid">
                        <label class="field">
                            <span>Nama lengkap *</span>
                            <input type="text" name="name" value="{{ old('name') }}" required maxlength="255">
                        </label>
                        <label class="field">
                            <span>NISN (10 digit)</span>
                            <input type="text" name="nisn" value="{{ old('nisn') }}" inputmode="numeric"
                                   pattern="[0-9]{10}" maxlength="10">
                        </label>
                        <label class="field">
                            <span>Kelas</span>
                            <input type="text" name="grade" value="{{ old('grade') }}" maxlength="20">
                        </label>
                        <label class="field">
                            <span>Peran</span>
                            <select name="role">
                                <option value="member">Anggota</option>
                                <option value="leader">Ketua</option>
                                <option value="reserve">Cadangan</option>
                            </select>
                        </label>
                    </div>
                    <div class="actions">
                        <button class="button secondary" type="submit">Tambah anggota</button>
                    </div>
                </form>
            @endif
            <p class="caption">NISN hanya terlihat oleh panitia, tidak untuk publik.</p>
        </section>

        <section class="card reveal" style="animation-delay:200ms">
            <span class="eyebrow">RIWAYAT · ARENA &amp; SKOR</span>
            <h2>Riwayat run</h2>
            <div class="data-list schedule-list">
                @forelse ($team->runs as $run)
                    <div class="data-row">
                        <div>
                            <strong>{{ $run->arena?->name }}</strong>
                            <span>
                                {{ $run->status->label() }}
                                @foreach ($run->scores as $score)
                                    · {{ $score->status->label() }}: {{ $score->total }}
                                @endforeach
                            </span>
                        </div>
                        <span class="status-badge purple">Run</span>
                    </div>
                @empty
                    <p>Belum ada run untuk tim ini.</p>
                @endforelse
            </div>
            <div class="actions">
                <a class="button secondary" href="{{ route('coach.teams.index') }}">← Kembali</a>
                <a class="button primary" href="{{ route('coach.results.show', $team) }}">Lihat hasil</a>
            </div>
        </section>
    </div>
</main>
@endsection
