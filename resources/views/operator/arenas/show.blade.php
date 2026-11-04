@extends('layouts.role')

@section('title', $arena->name)
@section('heading', 'Kontrol Arena — ' . $arena->name)

@section('content')
<a href="{{ route('operator.arenas.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>

<div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
            <tr>
                <th class="px-4 py-3 text-left">Tim</th>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Ubah Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($runs as $run)
                <tr>
                    <td class="px-4 py-3">{{ $run->team?->name }}<br><span class="text-xs text-slate-400">{{ $run->team?->school?->name }}</span></td>
                    <td class="px-4 py-3">{{ $run->category?->level->label() }}</td>
                    <td class="px-4 py-3">{{ $run->status->label() }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('operator.runs.status', $run) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="rounded-md border-slate-300 text-sm">
                                @foreach (\App\Enums\RunStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($run->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="outcome_reason" placeholder="Alasan (opsional)" class="w-40 rounded-md border-slate-300 text-sm">
                            <button class="rounded-md bg-slate-800 px-3 py-1.5 text-xs text-white">Simpan</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada run di arena ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
