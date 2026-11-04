@extends('layouts.role')

@section('title', 'Publikasi Hasil')
@section('heading', 'Publikasi Hasil')

@section('content')
<div class="rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
    Publikasi <strong>terpisah</strong> dari persetujuan. Kategori hanya dapat dipublikasikan bila semua run selesai
    dan semua skor disetujui. Alasan penghalang selalu ditampilkan di bawah.
</div>

<div class="mt-6 space-y-6">
    @foreach ($categories as $row)
        @php($category = $row['category'])
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-lg">{{ $category->level->label() }} — {{ $category->name }}</h2>
                    <p class="text-xs text-slate-500">Status: {{ $category->status->label() }}</p>
                </div>
                @if ($category->isPublished())
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs text-emerald-700">Dipublikasikan</span>
                @elseif ($row['publishable'])
                    <form method="POST" action="{{ route('chief.publication.publish', $category) }}">
                        @csrf
                        <button class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-400">Publikasikan</button>
                    </form>
                @else
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500">Belum dapat dipublikasikan</span>
                @endif
            </div>

            @if (! empty($row['reasons']))
                <ul class="mt-3 list-disc space-y-1 rounded-lg border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800">
                    @foreach ($row['reasons'] as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($row['standings']->isNotEmpty())
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-medium text-slate-600">Pratinjau klasemen</summary>
                    <table class="mt-2 w-full text-sm">
                        <thead class="text-slate-500">
                            <tr>
                                <th class="px-3 py-2 text-left">#</th>
                                <th class="px-3 py-2 text-left">Tim</th>
                                <th class="px-3 py-2 text-right">Skor Terbaik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($row['standings'] as $standing)
                                <tr>
                                    <td class="px-3 py-2">{{ $standing['rank'] }}</td>
                                    <td class="px-3 py-2">{{ $standing['team']->name }}</td>
                                    <td class="px-3 py-2 text-right font-mono">{{ $standing['best_total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            @endif
        </div>
    @endforeach
</div>
@endsection
