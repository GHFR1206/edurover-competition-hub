@extends('layouts.roborace-chief')

@section('title', 'Koreksi Resmi')

@php($page = 'correction')

@section('hero')@endsection
@section('hero-title', 'Koreksi hanya lewat jalur resmi.')
@section('hero-subtitle', 'Perbaiki skor yang sudah resmi beserta alasannya. Perubahan tercatat permanen untuk audit.')
@section('hero-mascot', 'd8b40.png')

@section('content')
<div class="warning official-warning">
    Area hasil resmi. Koreksi akan memperbarui skor publik, peringkat, dan rekap. Pastikan bukti sudah diperiksa.
</div>

@if (session('status'))
    <div class="badge green">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="warning">
        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

<div class="correction-grid">
    <section class="card">
        <h2>Buat koreksi resmi</h2>

        <form method="GET" action="{{ route('chief.correction.index') }}">
            <label for="score">Skor resmi</label>
            <select id="score" name="score" onchange="this.form.submit()">
                @foreach ($official as $option)
                    <option value="{{ $option->uuid }}" @selected($score && $score->id === $option->id)>
                        {{ $option->run?->team?->name }} · {{ $option->run?->category?->level->publicLabel() }} · {{ $option->total }} poin
                    </option>
                @endforeach
            </select>
        </form>

        @if ($score)
            <div class="badge green">✓ Resmi · Skor total saat ini {{ $score->total }}</div>

            <form method="POST" action="{{ route('chief.correction.store', $score) }}">
                @csrf
                @method('PATCH')

                <div class="rows">
                    @foreach ($score->lines as $i => $line)
                        <div class="data-row">
                            <div>
                                <strong>{{ $line->scoreObject?->name }}</strong>
                                <span>{{ $line->scoreObject?->type->label() }} · poin/unit {{ $line->points_per_unit_snapshot }} · lama ×{{ $line->quantity }}</span>
                                <input type="hidden" name="lines[{{ $i }}][line_id]" value="{{ $line->id }}">
                            </div>
                            <input type="number" min="0"
                                   @if ($line->scoreObject?->max_quantity) max="{{ $line->scoreObject->max_quantity }}" @endif
                                   name="lines[{{ $i }}][quantity]" value="{{ $line->quantity }}">
                        </div>
                    @endforeach
                </div>

                <label for="reason">Alasan koreksi *</label>
                <textarea id="reason" name="reason" required maxlength="255"
                          placeholder="Jelaskan alasan dan bukti koreksi..."></textarea>
                <small>* Alasan wajib. Ubah hanya baris yang memang salah.</small>

                <div class="actions">
                    <button class="button yellow" type="submit">Simpan koreksi</button>
                    <a class="button" href="{{ route('chief.correction.index') }}">Batal</a>
                </div>
            </form>
        @else
            <p class="muted">Belum ada skor resmi yang dapat dikoreksi.</p>
        @endif
    </section>

    <section class="card">
        <h2>Riwayat koreksi</h2>
        <div class="rows">
            @forelse ($history as $entry)
                <div class="data-row">
                    <div>
                        <strong>{{ $entry->score?->run?->team?->name }} · {{ $entry->score?->run?->category?->level->publicLabel() }}</strong>
                        <span>{{ $entry->decidedBy?->name }} · {{ $entry->decided_at?->translatedFormat('d M Y H:i') }} WIB · {{ $entry->reason }}</span>
                    </div>
                    <div class="score">{{ $entry->score?->total }}</div>
                </div>
            @empty
                <p class="muted">Belum ada koreksi resmi tercatat.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
