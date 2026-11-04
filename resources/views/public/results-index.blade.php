@extends('layouts.public')

@section('title', 'Hasil — NanoRobotic Competition Hub')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <h1 class="text-3xl font-bold">Hasil Kompetisi</h1>
    <p class="mt-2 text-slate-400">Hasil resmi hanya ditampilkan setelah dipublikasikan Chief Judge.</p>

    <div class="mt-8 space-y-4">
        @forelse ($categories as $category)
            @php($published = $category->isPublished())
            <a href="{{ $published ? route('public.results.category', $category) : '#' }}"
               class="block rounded-xl border border-slate-800 bg-slate-900 p-5 {{ $published ? 'hover:border-indigo-500' : 'opacity-60 cursor-not-allowed' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-lg">{{ $category->level->label() }} — {{ $category->name }}</h2>
                        <p class="text-xs text-slate-500">Persona: {{ $category->persona }}</p>
                    </div>
                    @if ($published)
                        <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs text-emerald-400">Dipublikasikan</span>
                    @else
                        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-400">Belum dipublikasikan</span>
                    @endif
                </div>
            </a>
        @empty
            <p class="text-slate-500">Belum ada kategori.</p>
        @endforelse
    </div>
</div>
@endsection
