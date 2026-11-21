@extends('layouts.roborace-juri')

@section('title', 'Penilaian Run')

@php $page = 'manual'; @endphp

@section('content')
<div class="page light">
    <header class="page-hero">
        <div>
            <div class="event-row">
                <span>● LANGSUNG · {{ now()->translatedFormat('d M Y') }}</span>
                <b>Seri Pilot</b>
            </div>
            <h1>Penilaian — {{ $run->team?->name }}</h1>
            <p>{{ $run->team?->school?->name }} · {{ $run->arena?->name }} · {{ $run->category?->level->publicLabel() }}</p>
        </div>
        <img class="hero-mascot" src="{{ asset('assets/17d7a.png') }}" alt="Maskot RoboRace">
    </header>
    <main class="manual-space">
        <div class="filters">
            <a class="chip" href="{{ route('judge.tasks.index') }}">← Kembali ke tugas</a>
            <span class="chip {{ $score?->status->value === 'approved' ? 'active' : '' }}">{{ $score?->status->label() ?? 'Belum dinilai' }}</span>
        </div>

        @if ($score && $score->status === \App\Enums\ScoreStatus::Approved)
            <div class="yellow-note">
                Skor ini sudah <strong>disetujui</strong> dan terkunci. Perubahan hanya melalui pembukaan ulang oleh Chief Judge.
            </div>
        @endif

        <section class="card detail-card">
            <div class="team-head">
                <span>
                    <code>{{ $run->team?->school?->name ?? 'SEKOLAH' }} · {{ $run->status->label() }}</code>
                    <h2>{{ $run->team?->name }} · {{ $run->category?->level->publicLabel() }}</h2>
                </span>
                <em class="{{ $score?->status->value === 'submitted' || $score?->status->value === 'approved' ? 'submitted' : '' }}">
                    {{ $score?->status->label() ?? 'Draft baru' }}
                </em>
            </div>
            <div class="meta-chips">
                <span>Arena {{ $run->arena?->name }}</span>
                <span>Level {{ $run->category?->level->publicLabel() }}</span>
                <span>Status run {{ $run->status->label() }}</span>
                @if ($run->official_time_ms !== null)
                    <span>⏱ Waktu arena {{ \App\Services\StandingsService::formatMs($run->official_time_ms) }}@if ($run->lap_count) · {{ $run->lap_count }} lap @endif</span>
                @endif
            </div>

            <form method="POST" action="{{ route('judge.scores.store', $run) }}" class="parameters" enctype="multipart/form-data">
                @csrf
                @php
                    $allObjects = $challenge?->scoreObjects ?? collect();
                    // filter() keeps the original keys so entry indexes stay
                    // unique across both sections.
                    $penaltyObjects = $allObjects->filter(fn ($o) => $o->type->isPenalty());
                    $manualObjects = $allObjects->filter(fn ($o) => ! $o->type->isPenalty());
                    $isLocked = $score && $score->status === \App\Enums\ScoreStatus::Approved;
                @endphp

                <section class="score-section">
                    <h3>Penilaian manual</h3>
                    <p>Isi jumlah dan centang objek yang tercapai. Poin mengikuti konfigurasi challenge pada level ini.</p>
                    @forelse ($manualObjects as $i => $object)
                        @php($line = $lines->get($object->id))
                        <div class="parameter">
                            <div>
                                <b>{{ $object->name }} <small>{{ $object->code }}</small></b>
                                <input type="hidden" name="entries[{{ $i }}][score_object_id]" value="{{ $object->id }}">
                                <input type="number" min="0" inputmode="numeric"
                                       @if ($object->max_quantity) max="{{ $object->max_quantity }}" @endif
                                       name="entries[{{ $i }}][quantity]"
                                       value="{{ old("entries.$i.quantity", $line?->quantity ?? 0) }}"
                                       @disabled($isLocked)>
                                <span>Poin/unit {{ $object->points_per_unit }} · batas {{ $object->max_quantity ?? '∞' }}</span>
                            </div>
                            <p>{{ $object->type->label() }}@if ($object->requires_evidence) · wajib bukti @endif</p>
                            <label class="check-row">
                                <input type="checkbox" name="entries[{{ $i }}][achieved]" value="1"
                                       @checked(old("entries.$i.achieved", $line?->achieved)) @disabled($isLocked)>
                                Tercapai
                            </label>
                            <label>
                                Catatan parameter (opsional)
                                <input type="text" name="entries[{{ $i }}][notes]" maxlength="255"
                                       value="{{ old("entries.$i.notes", $line?->notes) }}"
                                       placeholder="Catatan opsional untuk parameter ini." @disabled($isLocked)>
                            </label>
                        </div>
                    @empty
                        <p class="muted">Tantangan belum memiliki objek skor manual untuk dinilai.</p>
                    @endforelse
                </section>

                <section class="score-section penalty-section">
                    <h3>Penalti &amp; pelanggaran</h3>
                    <p>Centang pelanggaran yang terjadi. Setiap penalti mengurangi total skor run ini.</p>
                    @forelse ($penaltyObjects as $i => $object)
                        @php($line = $lines->get($object->id))
                        <div class="parameter">
                            <div>
                                <b>{{ $object->name }} <small>{{ $object->code }}</small></b>
                                <input type="hidden" name="entries[{{ $i }}][score_object_id]" value="{{ $object->id }}">
                                <input type="number" min="0" inputmode="numeric"
                                       @if ($object->max_quantity) max="{{ $object->max_quantity }}" @endif
                                       name="entries[{{ $i }}][quantity]"
                                       value="{{ old("entries.$i.quantity", $line?->quantity ?? 0) }}"
                                       @disabled($isLocked)>
                                <span>−{{ $object->points_per_unit }} poin/unit · batas {{ $object->max_quantity ?? '∞' }}</span>
                            </div>
                            <label class="check-row">
                                <input type="checkbox" name="entries[{{ $i }}][achieved]" value="1"
                                       @checked(old("entries.$i.achieved", $line?->achieved)) @disabled($isLocked)>
                                Pelanggaran terjadi
                            </label>
                            <label>
                                Catatan pelanggaran (opsional)
                                <input type="text" name="entries[{{ $i }}][notes]" maxlength="255"
                                       value="{{ old("entries.$i.notes", $line?->notes) }}"
                                       placeholder="Mis. menabrak dinding di tikungan 2." @disabled($isLocked)>
                            </label>
                        </div>
                    @empty
                        <p class="muted">Belum ada jenis penalti yang dikonfigurasi untuk level ini.</p>
                    @endforelse
                </section>

                @if ($score && $score->status !== \App\Enums\ScoreStatus::Approved)
                    <p class="muted">Total saat ini: <strong>{{ $score->total }}</strong> (subtotal {{ $score->subtotal }} − penalti {{ $score->penalty_total }})</p>
                @endif

                @if (! $score || $score->status !== \App\Enums\ScoreStatus::Approved)
                    <div class="evidence-block">
                        <h3>Bukti pendukung{{ $requiresEvidence ? ' (ada parameter wajib bukti)' : ' (opsional)' }}</h3>
                        <p class="muted">Unggah foto/video bukti arena. Berkas disimpan privat dan hanya dapat dilihat pihak berwenang.</p>
                        <label class="file-field">
                            <span>Berkas (maks 6 · jpg/png/webp/mp4/pdf · 20 MB)</span>
                            <input type="file" name="evidence_files[]" multiple accept="image/*,video/*,application/pdf">
                        </label>
                        <div class="meta-chips">
                            <label class="file-field">
                                <span>Jenis</span>
                                <select name="evidence_kind">
                                    <option value="photo">Foto</option>
                                    <option value="video">Video</option>
                                    <option value="document">Dokumen</option>
                                    <option value="note">Catatan</option>
                                </select>
                            </label>
                            <label class="file-field">
                                <span>Keterangan (opsional)</span>
                                <input type="text" name="evidence_caption" maxlength="255" placeholder="Mis. CP2 tercapai bersih">
                            </label>
                        </div>
                    </div>
                @endif

                @if ($evidence->isNotEmpty())
                    <div class="evidence-block">
                        <h3>Bukti terunggah ({{ $evidence->count() }})</h3>
                        <ul class="evidence-list">
                            @foreach ($evidence as $file)
                                <li>
                                    <a href="{{ route('evidence.show', $file) }}" target="_blank" rel="noopener">{{ $file->original_name ?? 'bukti' }}</a>
                                    <span class="muted">{{ strtoupper($file->kind) }} · {{ number_format(($file->size_bytes ?? 0) / 1024, 0) }} KB @if ($file->caption) · {{ $file->caption }} @endif</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($challenge && $challenge->scoreObjects->isNotEmpty() && (! $score || $score->status !== \App\Enums\ScoreStatus::Approved))
                    <div class="form-actions">
                        <button type="submit" class="chip active">Simpan Draft</button>
                        <span>Draft dapat diubah sebelum diajukan.</span>
                    </div>
                @endif
            </form>

            @if ($score && $score->status === \App\Enums\ScoreStatus::Draft)
                <form method="POST" action="{{ route('judge.scores.submit', $score) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-actions">
                        <button type="submit" class="chip active">Ajukan ke Chief Judge</button>
                        <span>Setelah diajukan, skor menunggu keputusan Chief Judge.</span>
                    </div>
                </form>
            @endif
        </section>

        <div class="mascot-message">
            <img src="{{ asset('assets/d8b40.png') }}" alt="">
            <b>Mayoritas bukan keputusan akhir. Chief akan memeriksa bukti sebelum mengesahkan atau meninjau ulang.</b>
        </div>
    </main>
</div>
@endsection
