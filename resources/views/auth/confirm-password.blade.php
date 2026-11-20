@extends('layouts.roborace-auth')

@section('title', 'Konfirmasi Kata Sandi · RoboRace')
@section('layout-class', 'auth-layout--compact')

@section('intro-title', 'Area aman')
@section('intro-description', 'Konfirmasi kata sandimu sebelum melanjutkan.')
@section('intro-mascot', '17d7a.png')

@section('content')
<form class="auth-card forgot-card" method="POST" action="{{ route('password.confirm') }}" novalidate>
    @csrf

    <h2>Konfirmasi kata sandi</h2>
    <p class="card-description">Ini area aman aplikasi. Mohon konfirmasi kata sandimu sebelum melanjutkan.</p>

    <label class="field">
        <span>Kata sandi</span>
        <div class="password-input @error('password') input-error @enderror">
            <input name="password" type="password" required autocomplete="current-password">
            <button type="button" class="eye-button" aria-label="Tampilkan kata sandi"
                    onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                <img src="{{ asset('assets/79260.svg') }}" alt="">
            </button>
        </div>
        @error('password') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <button class="submit-button" type="submit">Konfirmasi</button>
</form>
@endsection
