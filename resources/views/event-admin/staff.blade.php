@extends('layouts.roborace-event-admin')

@section('title', 'Staf & Penugasan')
@php($page = 'staff')
@section('hero-title', 'Tim di balik lintasan')
@section('hero-subtitle', 'Atur juri, operator, dan Chief Judge untuk kompetisi yang adil.')
@section('hero-mascot', 'dc500.png')

@section('content')
@if (session('status'))
    <div class="notice success">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="notice warning">
        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

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
    <h2>Tugaskan staf ke arena</h2>
    <p class="muted">Penugasan menentukan arena mana yang bisa dikontrol operator dan dinilai juri.</p>

    @if ($arenas->isEmpty())
        <p class="muted">Belum ada arena pada event ini. Tambahkan arena terlebih dahulu.</p>
    @else
        <form method="POST" action="{{ route('eventadmin.staff.store') }}">
            @csrf
            <div class="form-grid three">
                <label class="field"><span>Peran</span>
                    <select name="role" required>
                        <option value="operator" @selected(old('role') === 'operator')>Operator Arena</option>
                        <option value="judge" @selected(old('role') === 'judge')>Juri</option>
                    </select>
                </label>
                <label class="field"><span>Arena</span>
                    <select name="arena_id" required>
                        @foreach ($arenas as $arena)
                            <option value="{{ $arena->id }}" @selected(old('arena_id') == $arena->id)>
                                {{ $arena->name }} · {{ $arena->code }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="field"><span>Staf</span>
                    <select name="user_id" required>
                        @foreach ($operators as $operator)
                            <option value="{{ $operator->id }}" @selected(old('user_id') == $operator->id)>
                                {{ $operator->name }} · Operator
                            </option>
                        @endforeach
                        @foreach ($judges as $judge)
                            <option value="{{ $judge->id }}" @selected(old('user_id') == $judge->id)>
                                {{ $judge->name }} · Juri
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="actions">
                <button class="button yellow" type="submit">Tugaskan</button>
            </div>
        </form>
    @endif
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
                    <form method="POST" action="{{ route('eventadmin.staff.toggle', $activity) }}">
                        @csrf
                        @method('PATCH')
                        <button class="chip" type="submit">{{ $activity->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                    <form method="POST" action="{{ route('eventadmin.staff.destroy', $activity) }}"
                          onsubmit="return confirm('Hapus penugasan ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="chip" type="submit">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="muted">Belum ada penugasan. Tugaskan operator dan juri di form di atas.</p>
        @endforelse
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

<div class="mascot-note">
    <img src="{{ asset('assets/17d7a.png') }}" alt="">
    <strong>Setiap perubahan penugasan tercatat di audit log. Perubahan berlaku langsung pada akses arena staf.</strong>
</div>
@endsection
