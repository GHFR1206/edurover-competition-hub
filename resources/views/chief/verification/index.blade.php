@extends('layouts.role')

@section('title', 'Verifikasi Skor')
@section('heading', 'Verifikasi Skor')

@section('content')
<div class="rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
    <strong>Maker-checker:</strong> Anda tidak dapat menyetujui skor yang Anda tulis sendiri.
</div>

<div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Tim</th>
                <th class="px-4 py-3 text-left">Arena</th>
                <th class="px-4 py-3 text-left">Juri</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($scores as $score)
                <tr>
                    <td class="px-4 py-3">{{ $score->run?->team?->name }}<br><span class="text-xs text-slate-400">{{ $score->run?->category?->level->label() }}</span></td>
                    <td class="px-4 py-3">{{ $score->run?->arena?->name }}</td>
                    <td class="px-4 py-3">{{ $score->judge?->name }}</td>
                    <td class="px-4 py-3">{{ $score->status->label() }}</td>
                    <td class="px-4 py-3 text-right font-mono">{{ $score->total }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('chief.verification.show', $score) }}" class="text-indigo-600 hover:underline">Tinjau</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Tidak ada skor menunggu verifikasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
