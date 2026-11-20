@extends('layouts.roborace-auth')

@section('title', 'Daftar Sekolah · RoboRace')
@section('app-class', 'app--register')
@section('layout-class', 'auth-layout--register')
@section('footer-class', 'footer--register')

@section('intro-eyebrow', 'SATU SEKOLAH, BANYAK KEMUNGKINAN')
@section('intro-title', 'Ide hebat dimulai dari timmu!')
@section('intro-description', 'Buat akun, daftarkan sekolah, lalu ajak robotmu menjelajah lintasan.')
@section('intro-mascot', 'dc500.png')
@section('intro-note', 'Data sekolahmu membantu panitia memverifikasi tim. Isi dengan teliti, ya!')
@section('intro-note-mascot', 'e638f.png')

@section('content')
<form class="auth-card register-card" method="POST" action="{{ route('register') }}" novalidate>
    @csrf

    <span class="card-kicker">LANGKAH PERTAMA MENUJU ARENA</span>
    <h2>Daftar sekolah</h2>

    <label class="field">
        <span>Nama lengkap</span>
        <input name="name" type="text" value="{{ old('name') }}" placeholder="Rani Wulandari"
               required autofocus autocomplete="name" @if ($errors->has('name')) aria-invalid="true" @endif>
        @error('name') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Sekolah / Institusi</span>
        <input name="school" type="text" value="{{ old('school') }}" placeholder="SMP Garuda A">
        @error('school') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Email</span>
        <input name="email" type="email" value="{{ old('email') }}" placeholder="rani@garuda.contoh"
               required autocomplete="username" @if ($errors->has('email')) aria-invalid="true" @endif>
        @error('email') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Kata sandi</span>
        <div class="password-input @error('password') input-error @enderror">
            <input name="password" type="password" placeholder="••••••••" required autocomplete="new-password" id="register-password">
            <button type="button" class="eye-button" aria-label="Tampilkan kata sandi"
                    onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                <img src="{{ asset('assets/79260.svg') }}" alt="">
            </button>
        </div>
        @error('password') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Konfirmasi kata sandi</span>
        <input name="password_confirmation" type="password" placeholder="••••••••" required autocomplete="new-password">
    </label>

    <div class="strength" aria-label="Kekuatan kata sandi">
        @foreach (['Lemah', 'Cukup', 'Baik', 'Kuat'] as $label)
            <div><i></i><span>{{ $label }}</span></div>
        @endforeach
    </div>
    <div class="password-rules">
        <span>○ Minimal 8 karakter</span>
        <span>○ Huruf kecil (a-z)</span>
        <span>○ Huruf besar (A-Z)</span>
        <span>○ Angka (0-9)</span>
    </div>

    <button class="submit-button submit-button--yellow" type="submit">Daftar &amp; lanjut</button>

    <p class="terms">
        Dengan mendaftar, kamu menyetujui aturan kompetisi dan penggunaan data untuk verifikasi sekolah.
    </p>
    <p class="switch-page">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk →</a>
    </p>
</form>
@endsection
