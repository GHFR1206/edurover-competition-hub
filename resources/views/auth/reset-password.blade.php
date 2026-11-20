@extends('layouts.roborace-auth')

@section('title', 'Atur Ulang Kata Sandi · RoboRace')
@section('layout-class', 'auth-layout--compact')
@section('sky', 'cf19b.svg')

@section('intro-title', 'Kata sandi baru, semangat baru')
@section('intro-description', 'Buat kata sandi baru untuk kembali ke lintasan.')
@section('intro-mascot', '6db8f.png')

@section('content')
<form class="auth-card forgot-card" method="POST" action="{{ route('password.store') }}" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <h2>Atur ulang kata sandi</h2>
    <p class="card-description">Masukkan kata sandi barumu di bawah ini.</p>

    <label class="field">
        <span>Email</span>
        <input name="email" type="email" value="{{ old('email', $request->email) }}" required autocomplete="username"
               @if ($errors->has('email')) aria-invalid="true" @endif>
        @error('email') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Kata sandi baru</span>
        <div class="password-input @error('password') input-error @enderror">
            <input name="password" type="password" required autocomplete="new-password">
            <button type="button" class="eye-button" aria-label="Tampilkan kata sandi"
                    onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                <img src="{{ asset('assets/79260.svg') }}" alt="">
            </button>
        </div>
        @error('password') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>Konfirmasi kata sandi</span>
        <input name="password_confirmation" type="password" required autocomplete="new-password">
    </label>

    <button class="submit-button" type="submit">Simpan kata sandi</button>
    <a class="text-button back-button" href="{{ route('login') }}">← Kembali ke Masuk</a>
</form>
@endsection
