@extends('layouts.public')

@section('title', 'Klasemen ' . $category->level->label() . ' — NanoRobotic Competition Hub')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <a href="{{ route('public.results') }}" class="text-sm text-indigo-400 hover:text-indigo-300">&larr; Kembali</a>
    <h1 class="mt-4 text-3xl font-bold">Klasemen {{ $category->level->label() }}</h1>
    <p class="mt-1 text-slate-400">{{ $category->name }}</p>

    <div class="mt-8 overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-slate-400">
                <tr>
                    <th class="px-4 py-3 text-left">Peringkat</th>
                    <th class="px-4 py-3 text-left">Tim</th>
                    <th class="px-4 py-3 text-left">Sekolah</th>
                    <th class="px-4 py-3 text-right">Skor Terbaik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 bg-slate-950">
                @forelse ($standings as $row)
                    <tr>
                        <td class="px-4 py-3 font-semibold">{{ $row['rank'] }}</td>
                        <td class="px-4 py-3">{{ $row['team']->name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $row['team']->school?->name }}</td>
                        <td class="px-4 py-3 text-right font-mono">{{ $row['best_total'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada hasil.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
