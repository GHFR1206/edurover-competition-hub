@php
    $user = auth()->user();
    $nav = match (true) {
        $user->isRole(\App\Enums\Role::Coach) => [
            ['label' => 'Dashboard', 'route' => 'coach.dashboard'],
            ['label' => 'Tim Saya', 'route' => 'coach.teams.index'],
            ['label' => 'Hasil Tim', 'route' => 'coach.results.index'],
        ],
        $user->isRole(\App\Enums\Role::Judge) => [
            ['label' => 'Dashboard', 'route' => 'judge.dashboard'],
            ['label' => 'Tugas Penilaian', 'route' => 'judge.tasks.index'],
            ['label' => 'Skor Saya', 'route' => 'judge.scores.mine'],
        ],
        $user->isRole(\App\Enums\Role::ArenaOperator) => [
            ['label' => 'Dashboard', 'route' => 'operator.dashboard'],
            ['label' => 'Arena Saya', 'route' => 'operator.arenas.index'],
        ],
        $user->isRole(\App\Enums\Role::ChiefJudge) => [
            ['label' => 'Dashboard', 'route' => 'chief.dashboard'],
            ['label' => 'Verifikasi Skor', 'route' => 'chief.verification.index'],
            ['label' => 'Publikasi', 'route' => 'chief.publication.index'],
        ],
        default => [
            ['label' => 'Dashboard', 'route' => 'dashboard'],
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — NanoRobotic Hub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-900">
<div class="min-h-screen flex">
    <aside class="hidden lg:flex w-64 flex-col border-r border-slate-200 bg-white">
        <div class="h-16 flex items-center px-6 border-b border-slate-200">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold text-white">NR</span>
            <span class="ml-2 font-semibold">NanoRobotic</span>
        </div>
        <nav class="flex-1 p-3 space-y-1">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) || str_starts_with(request()->route()->getName() ?? '', explode('.', $item['route'])[0].'.') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-slate-200 p-4">
            <p class="text-sm font-medium">{{ $user->name }}</p>
            <p class="text-xs text-slate-500">{{ $user->getRoleNames()->first() }}</p>
            <div class="mt-3 flex gap-2">
                @if ($user->isStaff())
                    <a href="/admin" class="text-xs rounded-md bg-slate-800 px-3 py-1.5 text-white">Panel Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs rounded-md border border-slate-300 px-3 py-1.5">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center px-6">
            <h1 class="font-semibold text-lg">@yield('heading', 'Dashboard')</h1>
        </header>
        <main class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
