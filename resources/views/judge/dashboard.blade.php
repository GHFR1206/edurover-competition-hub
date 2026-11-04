@extends('layouts.role')

@section('title', 'Dashboard Juri')
@section('heading', 'Dashboard Juri')

@section('content')
<div class="grid gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Run di Arena Anda</p>
        <p class="text-3xl font-bold">{{ $assigned }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Run Selesai (siap dinilai)</p>
        <p class="text-3xl font-bold">{{ $pending }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Skor Diajukan</p>
        <p class="text-3xl font-bold">{{ $submitted }}</p>
    </div>
</div>

<a href="{{ route('judge.tasks.index') }}" class="mt-8 inline-block rounded-lg bg-indigo-500 px-5 py-3 font-medium text-white hover:bg-indigo-400">
    Lihat Tugas Penilaian
</a>
@endsection
