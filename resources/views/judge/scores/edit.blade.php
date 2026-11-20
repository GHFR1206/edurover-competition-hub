@extends('layouts.roborace-juri')

@section('title', 'Penilaian Run')

@php($page = 'manual')

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
            </div>

            <form method="POST" action="{{ route('judge.scores.store', $run) }}" class="parameters">
                @csrf
                <h3>Parameter manual</h3>
                <p>Isi jumlah dan centang objek yang tercapai. Poin mengikuti konfigurasi challenge pada level ini.</p>
                @forelse ($challenge?->scoreObjects ?? collect() as $i => $object)
                    @php($line = $lines->get($object->id))
                    <div class="parameter">
                        <div>
                            <b>{{ $object->name }} <small style="color:#8a68d4;font:11px 'JetBrains Mono'">{{ $object->code }}</small></b>
                            <input type="hidden" name="entries[{{ $i }}][score_object_id]" value="{{ $object->id }}">
                            <input type="number" min="0"
                                   @if ($object->max_quantity) max="{{ $object->max_quantity }}" @endif
                                   name="entries[{{ $i }}][quantity]"
                                   value="{{ old("entries.$i.quantity", $line?->quantity ?? 0) }}">
                            <span>Poin/unit {{ $object->points_per_unit }} · batas {{ $object->max_quantity ?? '∞' }}</span>
                        </div>
                        <p>{{ $object->type->label() }}@if ($object->requires_evidence) · wajib bukti @endif</p>
                        <label style="flex-direction:row;align-items:center;gap:10px">
                            <input type="checkbox" name="entries[{{ $i }}][achieved]" value="1"
                                   @checked(old("entries.$i.achieved", $line?->achieved))>
                            Tercapai
                        </label>
                        <label>
                            Catatan parameter (opsional)
                            <input type="text" name="entries[{{ $i }}][notes]" maxlength="255"
                                   value="{{ old("entries.$i.notes", $line?->notes) }}"
                                   placeholder="Catatan opsional untuk parameter ini.">
                        </label>
                    </div>
                @empty
                    <p class="muted">Tantangan belum memiliki objek skor untuk dinilai.</p>
                @endforelse

                @if ($score && $score->status !== \App\Enums\ScoreStatus::Approved)
                    <p class="muted">Total saat ini: <strong>{{ $score->total }}</strong> (subtotal {{ $score->subtotal }} − penalti {{ $score->penalty_total }})</p>
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
