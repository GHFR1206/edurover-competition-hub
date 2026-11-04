@extends('layouts.role')

@section('title', 'Hasil Tim')
@section('heading', 'Hasil Tim Saya')

@section('content')
<div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
    Skor <strong>Diajukan (belum resmi)</strong> masih dapat berubah. Skor <strong>Disetujui</strong> bersifat resmi.
    Nilai <strong>Draft</strong> tidak pernah ditampilkan kepada coach.
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($teams as $team)
        <a href="{{ route('coach.results.show', $team) }}" class="rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-400">
            <p class="font-semibold">{{ $team->name }}</p>
            <p class="text-xs text-slate-500">{{ $team->category->level->label() }} — {{ $team->school?->name }}</p>
        </a>
    @empty
        <p class="text-slate-500 text-sm">Belum ada tim.</p>
    @endforelse
</div>
@endsection
