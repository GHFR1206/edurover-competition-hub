@extends('layouts.roborace-super-admin')

@section('title', 'Pengguna')
@php($page = 'pengguna')

@section('content')
<div class="content decorated">
    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="notice error">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <section class="card">
        <h2>Undang Pengguna</h2>
        <form method="POST" action="{{ route('superadmin.users.store') }}">
            @csrf
            <div class="invite-grid">
                <label>Nama lengkap
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Maya Santoso" required>
                </label>
                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="maya@panitia.contoh" required>
                </label>
                <label>Peran
                    <select name="role" required>
                        @foreach ($roles as $role)
                            @continue($role === 'public')
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <button class="button yellow" type="submit" style="margin-top:16px">Undang Pengguna</button>
        </form>
    </section>

    <div class="search-line">
        <form method="GET" action="{{ route('superadmin.users.index') }}" style="display:flex;gap:16px;width:100%;align-items:end">
            <label style="flex:1">Cari pengguna
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau email...">
            </label>
            <label>Peran
                <select name="role" onchange="this.form.submit()">
                    <option value="">Semua peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected($roleFilter === $role)>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button" type="submit">Cari</button>
        </form>
    </div>

    <section class="card">
        <small class="eyebrow">{{ $users->where('is_active', true)->count() }} AKUN AKTIF · DATA TERKINI</small>
        <h2>Akun pengguna</h2>
        @forelse ($users as $user)
            <div class="user-row">
                <div class="user-head">
                    <div>
                        <strong>{{ $user->name }} · {{ $user->getRoleNames()->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', ') ?: 'Tanpa peran' }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                    <span class="status {{ $user->is_active ? 'green' : 'red' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="button-row">
                    @unless ($user->id === auth()->id())
                        <details class="role-editor">
                            <summary class="button">Ubah peran</summary>
                            <form method="POST" action="{{ route('superadmin.users.role', $user) }}">
                                @csrf
                                @method('PATCH')
                                <label>Peran baru
                                    <select name="role" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}" @selected($user->hasRole($role))>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Nama sekolah <small>(wajib bila memilih Coach)</small>
                                    <input type="text" name="school" value="{{ $user->coachProfile?->school?->name }}" placeholder="SMP Negeri 1">
                                </label>
                                <button class="button primary" type="submit">Simpan peran</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('superadmin.users.toggle', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button class="button {{ $user->is_active ? 'danger' : 'primary' }}" type="submit">
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="empty">Tidak ada pengguna yang cocok.</p>
        @endforelse
    </section>
</div>
@endsection
