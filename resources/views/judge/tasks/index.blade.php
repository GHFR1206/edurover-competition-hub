@extends('layouts.role')

@section('title', 'Tugas Penilaian')
@section('heading', 'Tugas Penilaian')

@section('content')
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Arena</th>
                <th class="px-4 py-3 text-left">Tim</th>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-left">Status Run</th>
                <th class="px-4 py-3 text-left">Skor Saya</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($runs as $run)
                @php($mine = $run->scores->firstWhere('judge_id', auth()->id()))
                <tr>
                    <td class="px-4 py-3">{{ $run->arena?->name }}</td>
                    <td class="px-4 py-3">{{ $run->team?->name }}<br><span class="text-xs text-slate-400">{{ $run->team?->school?->name }}</span></td>
                    <td class="px-4 py-3">{{ $run->category?->level->label() }}</td>
                    <td class="px-4 py-3">{{ $run->status->label() }}</td>
                    <td class="px-4 py-3">{{ $mine?->status->label() ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('judge.scores.edit', $run) }}" class="text-indigo-600 hover:underline">
                            {{ $mine ? 'Ubah' : 'Nilai' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada run di arena Anda.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
