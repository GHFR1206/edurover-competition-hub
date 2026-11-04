@extends('layouts.role')

@section('title', 'Skor Saya')
@section('heading', 'Skor Saya')

@section('content')
<div class="rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
    Anda hanya melihat skor yang Anda tulis sendiri. Skor juri lain tidak ditampilkan.
</div>

<div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Tim</th>
                <th class="px-4 py-3 text-left">Arena</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($scores as $score)
                <tr>
                    <td class="px-4 py-3">{{ $score->run?->team?->name }}</td>
                    <td class="px-4 py-3">{{ $score->run?->arena?->name }}</td>
                    <td class="px-4 py-3">{{ $score->status->label() }}</td>
                    <td class="px-4 py-3 text-right font-mono">{{ $score->total }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($score->status === \App\Enums\ScoreStatus::Draft)
                            <a href="{{ route('judge.scores.edit', $score->run) }}" class="text-indigo-600 hover:underline">Lanjutkan</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada skor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
