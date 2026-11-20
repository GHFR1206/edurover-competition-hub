@extends('layouts.roborace-auth')

@section('title', 'Masuk · RoboRace')

@section('intro-eyebrow', 'SELAMAT DATANG KEMBALI')
@section('intro-title', "Siap melaju,\nsiap jadi juara?")
@section('intro-description', 'Saatnya berkompetisi, berkolaborasi, dan mencetak prestasi!')
@section('intro-mascot', '17d7a.png')

@section('card-note', 'Siap berlomba? Mari berikan yang terbaik!')
@section('card-note-mascot', '17d7a.png')

@section('content')
<form class="auth-card login-card" method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    @if (session('status'))
        <div class="success-note">{{ session('status') }}</div>
    @endif

    <span class="card-kicker">MULAI DARI SINI</span>
    <h2>Masuk ke RoboRace</h2>
    <p class="card-description">Senang melihatmu kembali di lintasan!</p>

    <label class="field">
        <span>Email</span>
        <input name="email" type="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id"
               required autofocus autocomplete="username" @if ($errors->has('email')) aria-invalid="true" @endif>
        @error('email') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Kata sandi</span>
        <div class="password-input @error('password') input-error @enderror">
            <input name="password" type="password" placeholder="••••••••"
                   required autocomplete="current-password">
            <button type="button" class="eye-button" aria-label="Tampilkan kata sandi"
                    onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                <img src="{{ asset('assets/79260.svg') }}" alt="">
            </button>
        </div>
        @error('password') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <div class="form-options">
        <label class="remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            <span aria-hidden="true">✓</span>
            Ingat saya
        </label>
        @if (Route::has('password.request'))
            <a class="text-button" href="{{ route('password.request') }}">Lupa kata sandi?</a>
        @endif
    </div>

    <button class="submit-button" type="submit">Masuk</button>

    <p class="switch-page">
        Belum punya akun? <a href="{{ route('register') }}">Daftar sekolah →</a>
    </p>
    <div class="dotted-divider"></div>
</form>
@endsection
