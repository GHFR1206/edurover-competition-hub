@extends('layouts.role')

@section('title', $team->name)
@section('heading', 'Detail Tim')

@section('content')
<a href="{{ route('coach.teams.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Tim</p>
        <p class="text-xl font-bold">{{ $team->name }}</p>
        <p class="text-sm text-slate-500">{{ $team->school?->name }}</p>
        <p class="mt-2 text-xs">{{ $team->category->level->label() }} — {{ $team->category->name }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Anggota</p>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($team->participants as $participant)
                <li class="flex justify-between">
                    <span>{{ $participant->name }}</span>
                    <span class="text-slate-400">{{ $participant->role === 'leader' ? 'Ketua' : 'Anggota' }}</span>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 text-xs text-slate-400">NISN hanya terlihat oleh panitia, tidak untuk publik.</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Ringkasan Run</p>
        <p class="text-3xl font-bold">{{ $team->runs->count() }}</p>
        <p class="text-xs text-slate-500">total run tercatat</p>
    </div>
</div>

<h2 class="mt-8 font-semibold text-lg">Riwayat Run</h2>
<div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Arena</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Skor (belum resmi/resmi)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($team->runs as $run)
                <tr>
                    <td class="px-4 py-3">{{ $run->arena?->name }}</td>
                    <td class="px-4 py-3">{{ $run->status->label() }}</td>
                    <td class="px-4 py-3">
                        @foreach ($run->scores as $score)
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ $score->status->label() }}: {{ $score->total }}</span>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada run.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
