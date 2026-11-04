@extends('layouts.role')

@section('title', 'Dashboard Coach')
@section('heading', 'Dashboard Coach / Sekolah')

@section('content')
<div class="grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Tim Terdaftar</p>
        <p class="text-3xl font-bold">{{ $teams->count() }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Skor Menunggu Verifikasi</p>
        <p class="text-3xl font-bold">{{ $pending }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Sekolah</p>
        <p class="text-lg font-semibold">{{ auth()->user()->coachProfile?->school?->name ?? '—' }}</p>
    </div>
</div>

<h2 class="mt-8 font-semibold text-lg">Tim Saya</h2>
<div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($teams as $team)
        <a href="{{ route('coach.teams.show', $team) }}" class="rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-400">
            <div class="flex items-center justify-between">
                <p class="font-semibold">{{ $team->name }}</p>
                <span class="text-xs rounded-full bg-slate-100 px-2 py-1">{{ $team->category->level->label() }}</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">{{ $team->runs->count() }} run</p>
        </a>
    @empty
        <p class="text-slate-500 text-sm">Belum ada tim terdaftar untuk sekolah Anda.</p>
    @endforelse
</div>
@endsection
