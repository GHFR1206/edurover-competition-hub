@extends('layouts.role')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="rounded-xl border border-slate-200 bg-white p-6">
    <h2 class="font-semibold text-lg">Selamat datang, {{ auth()->user()->name }}</h2>
    <p class="mt-1 text-sm text-slate-500">Peran: {{ auth()->user()->getRoleNames()->first() }}</p>

    @if ($event)
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-4">
                <p class="text-xs text-slate-500">Event Aktif</p>
                <p class="font-semibold">{{ $event->name }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4">
                <p class="text-xs text-slate-500">Kategori</p>
                <p class="text-2xl font-bold">{{ $event->categories->count() }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 p-4">
                <p class="text-xs text-slate-500">Status Event</p>
                <p class="font-semibold">{{ ucfirst($event->status) }}</p>
            </div>
        </div>
    @endif

    @if (auth()->user()->isStaff())
        <a href="/admin" class="mt-6 inline-block rounded-lg bg-indigo-500 px-5 py-3 font-medium text-white hover:bg-indigo-400">
            Buka Panel Admin
        </a>
    @endif
</div>
@endsection
