<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NanoRobotic Competition Hub')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold">NR</span>
                        <span class="font-semibold text-lg">NanoRobotic Hub</span>
                    </a>
                    <nav class="flex items-center gap-6 text-sm">
                        <a href="{{ route('home') }}" class="text-slate-300 hover:text-white">Beranda</a>
                        <a href="{{ route('public.results') }}" class="text-slate-300 hover:text-white">Hasil</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-500 px-3 py-2 font-medium text-white hover:bg-indigo-400">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md bg-indigo-500 px-3 py-2 font-medium text-white hover:bg-indigo-400">Masuk</a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
            NanoRobotic Competition Hub — data demo. NISN peserta tidak pernah ditampilkan kepada publik.
        </footer>
    </div>
</body>
</html>
