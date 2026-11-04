@extends('layouts.role')

@section('title', 'Arena Saya')
@section('heading', 'Arena Saya')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($arenas as $arena)
        <a href="{{ route('operator.arenas.show', $arena) }}" class="rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-400">
            <div class="flex items-center justify-between">
                <p class="font-semibold">{{ $arena->name }}</p>
                <span class="text-xs rounded-full bg-slate-100 px-2 py-1">{{ $arena->code }}</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">{{ $arena->location }}</p>
            <p class="mt-2 text-xs">Status: {{ ucfirst($arena->status) }}</p>
        </a>
    @empty
        <p class="text-slate-500 text-sm">Anda belum ditugaskan ke arena mana pun.</p>
    @endforelse
</div>
@endsection
