@extends('layouts.role')

@section('title', 'Dashboard Operator')
@section('heading', 'Dashboard Operator Arena')

@section('content')
<div class="grid gap-4 sm:grid-cols-2">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Arena Ditugaskan</p>
        <p class="text-3xl font-bold">{{ $arenas->count() }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs text-slate-500">Run Aktif</p>
        <p class="text-3xl font-bold">{{ $activeRuns->count() }}</p>
    </div>
</div>

<h2 class="mt-8 font-semibold text-lg">Run Aktif</h2>
<div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Arena</th>
                <th class="px-4 py-3 text-left">Tim</th>
                <th class="px-4 py-3 text-left">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($activeRuns as $run)
                <tr>
                    <td class="px-4 py-3">{{ $run->arena?->name }}</td>
                    <td class="px-4 py-3">{{ $run->team?->name }}</td>
                    <td class="px-4 py-3">{{ $run->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Tidak ada run aktif.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
