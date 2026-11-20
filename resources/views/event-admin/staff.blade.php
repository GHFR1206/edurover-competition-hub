@extends('layouts.roborace-event-admin')

@section('title', 'Staf & Penugasan')
@php($page = 'staff')
@section('hero-title', 'Tim di balik lintasan')
@section('hero-subtitle', 'Atur juri, operator, dan Chief Judge untuk kompetisi yang adil.')
@section('hero-mascot', 'dc500.png')

@section('content')
<section class="card">
    <h2>Jumlah juri per level</h2>
    <div class="jury-grid">
        @foreach (\App\Enums\Level::cases() as $level)
            @php($count = $activities->where('role', 'judge')->count())
            <div class="jury">
                <span>{{ $level->publicLabel() }}</span>
                <div><strong>{{ $panelSize }}</strong></div>
                <b>Kuorum {{ $quorum }} dari {{ max($panelSize, 1) }} juri</b>
                <small>Mayoritas suara membantu review.<br>Keputusan akhir tetap pada Chief Judge.</small>
            </div>
        @endforeach
    </div>
</section>

<section class="card">
    <h2>Penugasan aktif</h2>
    <div class="rows">
        @forelse ($activities as $activity)
            <div class="row">
                <div class="row-copy">
                    <strong>{{ $activity->user?->name ?? 'Pengguna' }} · {{ ucwords(str_replace('_', ' ', $activity->role)) }}</strong>
                    <span>{{ $activity->arena?->name ?? 'Arena' }} · {{ $activity->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="row-actions">
                    <span class="badge {{ $activity->is_active ? 'badge-aktif' : 'badge-◔-sementara' }}">
                        {{ $activity->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        @empty
            <p class="muted">Belum ada penugasan. Tambahkan staf di panel admin.</p>
        @endforelse
    </div>
</section>

<section class="card">
    <h2>Undang staf</h2>
    <p class="muted">Undangan dan penugasan staf dilakukan dari panel admin agar tercatat di audit log.</p>
    <div class="row-actions">
        <a class="button yellow" href="/admin">Buka panel admin</a>
    </div>
</section>

<section class="card">
    <h2>Daftar staf tersedia</h2>
    <div class="form-grid two">
        <div class="row"><div class="row-copy"><strong>Juri</strong><span>{{ $judges->count() }} akun</span></div></div>
        <div class="row"><div class="row-copy"><strong>Operator Arena</strong><span>{{ $operators->count() }} akun</span></div></div>
        <div class="row"><div class="row-copy"><strong>Chief Judge</strong><span>{{ $chiefs->count() }} akun</span></div></div>
        <div class="row"><div class="row-copy"><strong>Arena</strong><span>{{ $arenas->count() }} arena pada event ini</span></div></div>
    </div>
</section>
@endsection
