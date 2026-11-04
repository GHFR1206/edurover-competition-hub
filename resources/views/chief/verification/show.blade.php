@extends('layouts.role')

@section('title', 'Tinjau Skor')
@section('heading', 'Tinjau Skor — ' . $score->run?->team?->name)

@section('content')
<a href="{{ route('chief.verification.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>

<div class="mt-4 grid gap-4 sm:grid-cols-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Arena</p>
        <p class="font-semibold">{{ $score->run?->arena?->name }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Juri</p>
        <p class="font-semibold">{{ $score->judge?->name }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Status</p>
        <p class="font-semibold">{{ $score->status->label() }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Total</p>
        <p class="text-2xl font-bold">{{ $score->total }}</p>
        <p class="text-xs text-slate-400">subtotal {{ $score->subtotal }} − penalti {{ $score->penalty_total }}</p>
    </div>
</div>

<div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Objek Skor</th>
                <th class="px-4 py-3 text-right">Poin/Unit</th>
                <th class="px-4 py-3 text-center">Jumlah</th>
                <th class="px-4 py-3 text-right">Dihitung</th>
                <th class="px-4 py-3 text-left">Catatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($score->lines as $line)
                <tr>
                    <td class="px-4 py-3">{{ $line->scoreObject?->name }}</td>
                    <td class="px-4 py-3 text-right">{{ $line->points_per_unit_snapshot }}</td>
                    <td class="px-4 py-3 text-center">{{ $line->quantity }}@if ($line->achieved) ✓ @endif</td>
                    <td class="px-4 py-3 text-right font-mono">{{ $line->computed_points }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $line->notes }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Tidak ada baris skor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($score->approvals->isNotEmpty())
    <h2 class="mt-8 font-semibold text-lg">Riwayat Keputusan</h2>
    <ul class="mt-2 space-y-2 text-sm">
        @foreach ($score->approvals as $approval)
            <li class="rounded-lg border border-slate-200 bg-white px-4 py-2">
                <span class="font-medium">{{ ucfirst($approval->decision) }}</span>
                oleh {{ $approval->decidedBy?->name }} — {{ $approval->decided_at?->format('d M Y H:i') }}
                @if ($approval->reason)<br><span class="text-slate-500">{{ $approval->reason }}</span>@endif
            </li>
        @endforeach
    </ul>
@endif

<div class="mt-8 grid gap-4 lg:grid-cols-3">
    <form method="POST" action="{{ route('chief.verification.approve', $score) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
        @csrf
        @method('PATCH')
        <p class="font-semibold text-emerald-800">Setujui</p>
        <p class="mt-1 text-xs text-emerald-700">Skor menjadi resmi. Hanya jika Anda bukan penulisnya.</p>
        <button @disabled($score->judge_id === auth()->id()) class="mt-3 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-40">
            Setujui Skor
        </button>
    </form>

    <form method="POST" action="{{ route('chief.verification.reject', $score) }}" class="rounded-xl border border-rose-200 bg-rose-50 p-5">
        @csrf
        @method('PATCH')
        <p class="font-semibold text-rose-800">Tolak</p>
        <input type="text" name="reason" required placeholder="Alasan penolakan" class="mt-2 w-full rounded-md border-slate-300 text-sm">
        <button class="mt-3 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white">Tolak Skor</button>
    </form>

    <form method="POST" action="{{ route('chief.verification.reopen', $score) }}" class="rounded-xl border border-slate-200 bg-slate-50 p-5">
        @csrf
        @method('PATCH')
        <p class="font-semibold">Buka Ulang</p>
        <input type="text" name="reason" required placeholder="Alasan pembukaan ulang" class="mt-2 w-full rounded-md border-slate-300 text-sm">
        <button class="mt-3 rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white">Buka Ulang</button>
    </form>
</div>
@endsection
