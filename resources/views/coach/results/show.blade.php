@extends('layouts.role')

@section('title', 'Hasil ' . $team->name)
@section('heading', 'Hasil — ' . $team->name)

@section('content')
<a href="{{ route('coach.results.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>

<div class="mt-4 space-y-4">
    @forelse ($runs as $run)
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">{{ $run->arena?->name }}</p>
                    <p class="text-xs text-slate-500">Status run: {{ $run->status->label() }}</p>
                </div>
                @if ($run->scores->isNotEmpty())
                    <span class="text-2xl font-bold">{{ $run->scores->max('total') }}</span>
                @else
                    <span class="text-xs text-slate-400">Belum ada skor terlihat</span>
                @endif
            </div>

            @foreach ($run->scores as $score)
                <div class="mt-3 rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium">{{ $score->status->label() }}</span>
                        <span>Total: {{ $score->total }} (subtotal {{ $score->subtotal }} − penalti {{ $score->penalty_total }})</span>
                    </div>
                    <ul class="mt-2 space-y-1 text-xs text-slate-600">
                        @foreach ($score->lines as $line)
                            <li class="flex justify-between">
                                <span>{{ $line->scoreObject?->name }}</span>
                                <span>{{ $line->computed_points }} (×{{ $line->quantity }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-slate-500">Belum ada run.</p>
    @endforelse
</div>
@endsection
