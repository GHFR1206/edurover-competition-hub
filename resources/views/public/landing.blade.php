@extends('layouts.public')

@section('title', 'Beranda — NanoRobotic Competition Hub')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div>
            <p class="text-indigo-400 font-semibold tracking-wide uppercase text-sm">Kompetisi Robotika Edukasi</p>
            <h1 class="mt-3 text-4xl sm:text-5xl font-bold leading-tight">
                {{ $event?->name ?? 'NanoRobotic Competition Hub' }}
            </h1>
            <p class="mt-4 text-slate-400 text-lg">
                {{ $event?->description ?? 'Platform manajemen kompetisi robot EduRover untuk jenjang SD, SMP, dan SMA.' }}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('public.results') }}" class="rounded-lg bg-indigo-500 px-5 py-3 font-medium text-white hover:bg-indigo-400">
                    Lihat Hasil
                </a>
                <a href="{{ route('login') }}" class="rounded-lg border border-slate-700 px-5 py-3 font-medium text-slate-200 hover:bg-slate-800">
                    Masuk Petugas
                </a>
            </div>

            @if ($event)
                <dl class="mt-10 grid grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                        <dt class="text-xs text-slate-500">Kategori</dt>
                        <dd class="text-2xl font-bold">{{ $categories->count() }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                        <dt class="text-xs text-slate-500">Tim</dt>
                        <dd class="text-2xl font-bold">{{ $teamCount }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                        <dt class="text-xs text-slate-500">Arena</dt>
                        <dd class="text-2xl font-bold">{{ $arenaCount }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="font-semibold text-lg mb-4">Kategori Lomba</h2>
            <div class="space-y-3">
                @forelse ($categories as $category)
                    <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950 px-4 py-3">
                        <div>
                            <p class="font-medium">{{ $category->level->label() }} — {{ $category->persona }}</p>
                            <p class="text-xs text-slate-500">{{ $category->description }}</p>
                        </div>
                        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs">{{ $category->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">Belum ada kategori.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
