@extends('layouts.role')

@section('title', 'Penilaian Run')
@section('heading', 'Penilaian — ' . $run->team?->name)

@section('content')
<a href="{{ route('judge.tasks.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke tugas</a>

<div class="mt-4 grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Arena</p>
        <p class="font-semibold">{{ $run->arena?->name }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Sekolah</p>
        <p class="font-semibold">{{ $run->team?->school?->name }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Status Run</p>
        <p class="font-semibold">{{ $run->status->label() }}</p>
    </div>
</div>

@if ($score && $score->status === \App\Enums\ScoreStatus::Approved)
    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        Skor ini sudah <strong>disetujui</strong> dan terkunci. Perubahan hanya melalui pembukaan ulang oleh Chief Judge.
    </div>
@endif

<form method="POST" action="{{ route('judge.scores.store', $run) }}" class="mt-6">
    @csrf

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Objek Skor</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-right">Poin/Unit</th>
                    <th class="px-4 py-3 text-center">Jumlah</th>
                    <th class="px-4 py-3 text-center">Tercapai</th>
                    <th class="px-4 py-3 text-left">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($challenge?->scoreObjects ?? collect() as $i => $object)
                    @php($line = $lines->get($object->id))
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $object->code }}</td>
                        <td class="px-4 py-3">
                            {{ $object->name }}
                            @if ($object->requires_evidence)
                                <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-700">wajib bukti</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $object->type->label() }}</td>
                        <td class="px-4 py-3 text-right">{{ $object->points_per_unit }}</td>
                        <td class="px-4 py-3 text-center">
                            <input type="hidden" name="entries[{{ $i }}][score_object_id]" value="{{ $object->id }}">
                            <input type="number" min="0"
                                   @if ($object->max_quantity) max="{{ $object->max_quantity }}" @endif
                                   name="entries[{{ $i }}][quantity]"
                                   value="{{ old("entries.$i.quantity", $line?->quantity ?? 0) }}"
                                   class="w-20 rounded-md border-slate-300 text-center text-sm">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" name="entries[{{ $i }}][achieved]" value="1"
                                   @checked(old("entries.$i.achieved", $line?->achieved))
                                   class="rounded border-slate-300">
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" name="entries[{{ $i }}][notes]" maxlength="255"
                                   value="{{ old("entries.$i.notes", $line?->notes) }}"
                                   class="w-full rounded-md border-slate-300 text-sm">
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Tantangan belum memiliki objek skor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($score && $score->status !== \App\Enums\ScoreStatus::Approved)
        <p class="mt-3 text-sm text-slate-500">Total saat ini: <strong>{{ $score->total }}</strong> (subtotal {{ $score->subtotal }} − penalti {{ $score->penalty_total }})</p>
    @endif

    <div class="mt-4 flex gap-3">
        @if ($challenge && $challenge->scoreObjects->isNotEmpty() && (! $score || $score->status !== \App\Enums\ScoreStatus::Approved))
            <button type="submit" class="rounded-lg bg-slate-800 px-5 py-3 font-medium text-white hover:bg-slate-700">
                Simpan Draft
            </button>
        @endif
    </div>
</form>

@if ($score && $score->status === \App\Enums\ScoreStatus::Draft)
    <form method="POST" action="{{ route('judge.scores.submit', $score) }}" class="mt-3">
        @csrf
        @method('PATCH')
        <button class="rounded-lg bg-indigo-500 px-5 py-3 font-medium text-white hover:bg-indigo-400">
            Ajukan ke Chief Judge
        </button>
    </form>
@endif
@endsection
