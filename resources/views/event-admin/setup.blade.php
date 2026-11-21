@extends('layouts.roborace-event-admin')

@section('title', 'Setup Event')
@php($page = 'setup')
@section('hero-title', 'Siapkan tantangannya!')
@section('hero-subtitle', ($event?->name ?? 'Belum ada event') . ' · Setup sebelum pendaftaran dibuka.')
@section('hero-mascot', '17d7a.png')

@section('content')
@php($allComplete = collect($checks)->every(fn ($c) => $c[1]))

@if (session('status'))
    <div class="notice">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="notice error">
        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

<div class="chips setup-tabs">
    <a class="chip active" href="#kategori">1 · Kategori</a>
    <a class="chip" href="#ruleset">2 · Ruleset</a>
    <a class="chip" href="#challenge">3 · Challenge</a>
</div>

<section class="card" id="kategori">
    <span class="eyebrow">JENJANG PESERTA</span>
    <h2>1 · Kategori</h2>
    <p class="muted">Aktifkan level yang dipertandingkan dan lengkapi setiap ruleset. Publikasi hasil tetap dilakukan Chief Judge.</p>
    <div class="rules">
        @forelse ($categories as $category)
            <div class="rule">
                <span class="rule-level">▣ {{ $category->level->publicLabel() }}</span>
                <div>
                    <strong>{{ $category->teams()->count() }}</strong>
                    <span>Tim</span>
                </div>
                <div class="rule-status">
                    <span>{{ $category->status->label() }}</span>
                    <span>Status</span>
                </div>
                <div class="rule-actions">
                    @if ($category->isPublished())
                        <span class="badge badge-✓-resmi">Dipublikasikan</span>
                    @else
                        <form method="POST" action="{{ route('eventadmin.setup.category', $category) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" aria-label="Status kategori {{ $category->level->publicLabel() }}">
                                @foreach ($editableStatuses as $status)
                                    <option value="{{ $status->value }}" @selected($category->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="muted">Belum ada kategori. Tambahkan kategori di panel admin.</p>
        @endforelse
    </div>
    <span class="mini-success">{{ $categories->count() }} kategori aktif</span>
</section>

<section class="card" id="ruleset">
    <span class="eyebrow">BOBOT TERKUNCI · TOTAL 100%</span>
    <h2>2 · Ruleset</h2>
    <p class="muted">Bobot kriteria mengikuti jenjang. Nilai manual tetap 0–20, ditambahkan ke poin robot yang disahkan.</p>
    <div class="rules">
        @forelse ($categories as $category)
            @php($rule = $category->rulebooks->sortByDesc('version')->first())
            <div class="rule">
                <span class="rule-level">▣ {{ $category->level->publicLabel() }}</span>
                <div>
                    <strong>{{ $rule?->version ? 'v'.$rule->version : '—' }}</strong>
                    <span>Versi</span>
                </div>
                <div>
                    <strong>{{ $rule?->status ?? 'draft' }}</strong>
                    <span>Status</span>
                </div>
                <div>
                    <strong>{{ $rule?->locked_at ? '🔒' : '○' }}</strong>
                    <span>{{ $rule?->locked_at ? 'Terkunci' : 'Belum kunci' }}</span>
                </div>
            </div>
        @empty
            <p class="muted">Belum ada ruleset.</p>
        @endforelse
    </div>
</section>

<section class="card" id="challenge">
    <span class="eyebrow">PILIH LEVEL, ATUR RITME</span>
    <h2>3 · Challenge</h2>
    <div class="rows">
        @foreach ($categories as $category)
            @php($challenge = $category->challenges->first())
            <div class="row">
                <div class="row-copy">
                    <strong>{{ $category->level->publicLabel() }} · {{ $challenge?->name ?? 'Challenge belum dibuat' }}</strong>
                    <span>{{ $challenge?->description ?? 'Lengkapi challenge di panel admin.' }}</span>
                </div>
                <div class="row-actions">
                    <span class="badge {{ $challenge ? 'badge-aktif' : 'badge-◔-sementara' }}">
                        {{ $challenge ? 'Lengkap' : 'Perlu dilengkapi' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="card">
    <h2>Periksa sebelum membuka</h2>
    <ul class="checklist">
        @foreach ($checks as $check)
            <li class="{{ $check[1] ? 'done' : '' }}">{{ $check[1] ? '✓' : '○' }} {{ $check[0] }}</li>
        @endforeach
    </ul>
    @unless ($allComplete)
        <div class="notice warning">
            Satu langkah lagi! Semua kategori harus punya challenge lengkap sebelum pendaftaran dibuka.
        </div>
    @endunless
    @if ($event)
        <form method="POST" action="{{ route('eventadmin.events.advance', $event) }}">
            @csrf
            @method('PATCH')
            <button class="button yellow" type="submit" @disabled(! $allComplete)>Buka pendaftaran</button>
        </form>
    @endif
</section>
@endsection
