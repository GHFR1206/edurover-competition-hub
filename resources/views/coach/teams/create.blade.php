@extends('layouts.roborace-coach')

@section('title', 'Daftarkan Tim')

@php($page = 'team')

@section('content')
<section class="hero hero-schedule">
    <div class="content-width">
        <div class="event-row">
            <span class="live-chip">● PENDAFTARAN · {{ now()->translatedFormat('d M Y') }}</span>
            <span class="series-chip">Seri Pilot</span>
        </div>
        <div class="hero-row">
            <div>
                <h1>Rakit tim, lalu melesat.</h1>
                <p>Isi data tim dan anggotanya. Setelah diajukan, panitia akan memverifikasi sebelum tim bisa bertanding.</p>
            </div>
            <img class="hero-mascot schedule-mascot" src="{{ asset('assets/74589.png') }}" alt="">
        </div>
    </div>
</section>

<main class="content-zone">
    <div class="content-width page-stack">
        @if ($categories->isEmpty())
            <section class="card reveal">
                <span class="eyebrow">PENDAFTARAN DITUTUP</span>
                <h2>Belum ada kategori yang membuka pendaftaran</h2>
                <p>Pendaftaran tim dibuka saat panitia menyiapkan kategori lomba. Silakan cek kembali nanti.</p>
                <div class="actions">
                    <a class="button secondary" href="{{ route('coach.teams.index') }}">← Kembali ke tim saya</a>
                </div>
            </section>
        @else
            <form method="POST" action="{{ route('coach.teams.store') }}">
                @csrf

                <section class="card reveal">
                    <span class="eyebrow">DATA TIM</span>
                    <h2>Identitas tim</h2>
                    <div class="form-grid">
                        <label class="field">
                            <span>Kategori lomba *</span>
                            <select name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->level->publicLabel() }} · {{ $category->level->challengeName() }}
                                        ({{ $category->min_team_members }}–{{ $category->max_team_members }} anggota)
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>Nama tim *</span>
                            <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required
                                   placeholder="Contoh: Garuda Alpha">
                        </label>
                        <label class="field">
                            <span>Nama singkat (opsional)</span>
                            <input type="text" name="short_name" value="{{ old('short_name') }}" maxlength="10"
                                   placeholder="GRD">
                        </label>
                    </div>
                </section>

                <section class="card reveal" style="animation-delay:80ms">
                    <span class="eyebrow">ANGGOTA · MINIMAL 1</span>
                    <h2>Anggota tim</h2>
                    <p class="caption">NISN hanya dipakai panitia untuk verifikasi dan tidak pernah tampil di halaman publik.</p>

                    @for ($i = 0; $i < 4; $i++)
                        <div class="member-block">
                            <h3>Anggota {{ $i + 1 }} {{ $i === 0 ? '(ketua)' : '' }}</h3>
                            <div class="form-grid">
                                <label class="field">
                                    <span>Nama lengkap {{ $i === 0 ? '*' : '' }}</span>
                                    <input type="text" name="members[{{ $i }}][name]"
                                           value="{{ old("members.$i.name") }}"
                                           @if ($i === 0) required @endif
                                           placeholder="Nama sesuai kartu pelajar">
                                </label>
                                <label class="field">
                                    <span>NISN (10 digit)</span>
                                    <input type="text" name="members[{{ $i }}][nisn]"
                                           value="{{ old("members.$i.nisn") }}" inputmode="numeric"
                                           pattern="[0-9]{10}" maxlength="10" placeholder="0012345678">
                                </label>
                                <label class="field">
                                    <span>Kelas</span>
                                    <input type="text" name="members[{{ $i }}][grade]"
                                           value="{{ old("members.$i.grade") }}" maxlength="20" placeholder="8A">
                                </label>
                                <label class="field">
                                    <span>Peran</span>
                                    <select name="members[{{ $i }}][role]">
                                        <option value="leader" @selected(old("members.$i.role") === 'leader' || $i === 0)>Ketua</option>
                                        <option value="member" @selected(old("members.$i.role") === 'member' && $i !== 0)>Anggota</option>
                                        <option value="reserve" @selected(old("members.$i.role") === 'reserve')>Cadangan</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    @endfor
                </section>

                <div class="notice warning">
                    Pastikan jumlah anggota sesuai aturan kategori. Tim yang sudah diajukan tidak dapat diubah
                    sampai panitia memberi keputusan.
                </div>

                <div class="actions">
                    <button class="button primary" type="submit">Simpan sebagai draft</button>
                    <a class="button secondary" href="{{ route('coach.teams.index') }}">Batalkan</a>
                </div>
            </form>
        @endif
    </div>
</main>
@endsection
