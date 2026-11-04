@extends('layouts.role')

@section('title', 'Tim Saya')
@section('heading', 'Tim Saya')

@section('content')
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Tim</th>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-left">Sekolah</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($teams as $team)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $team->name }}</td>
                    <td class="px-4 py-3">{{ $team->category->level->label() }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $team->school?->name }}</td>
                    <td class="px-4 py-3">{{ $team->status->label() }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('coach.teams.show', $team) }}" class="text-indigo-600 hover:underline">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada tim.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
