@extends('layouts.roborace-auth')

@section('title', 'Lupa Kata Sandi · RoboRace')
@section('layout-class', 'auth-layout--compact')
@section('sky', 'cf19b.svg')

@section('intro-title', 'Tenang, kita cari jalannya')
@section('intro-description', 'Masukkan emailmu dan kami kirim tautan untuk membuat kata sandi baru.')
@section('intro-mascot', '6db8f.png')

@section('content')
<form class="auth-card forgot-card" method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf

    @if (session('status'))
        <div class="success-note">{{ session('status') }}</div>
    @endif

    <h2>Lupa kata sandi</h2>
    <p class="card-description">Tautan berlaku 30 menit.</p>

    <label class="field">
        <span>Email</span>
        <input name="email" type="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id"
               required autofocus autocomplete="username" @if ($errors->has('email')) aria-invalid="true" @endif>
        @error('email') <small class="field-error">{{ $message }}</small> @enderror
    </label>

    <button class="submit-button" type="submit">Kirim tautan reset</button>
    <a class="text-button back-button" href="{{ route('login') }}">← Kembali ke Masuk</a>
</form>
@endsection
