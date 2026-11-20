@extends('layouts.roborace-auth')

@section('title', 'Verifikasi Email · RoboRace')
@section('layout-class', 'auth-layout--compact')

@section('intro-title', 'Satu langkah lagi menuju arena')
@section('intro-description', 'Verifikasi emailmu agar panitia dapat memproses data timmu.')
@section('intro-mascot', '17d7a.png')

@section('content')
<div class="auth-card check-card">
    <h2>Cek emailmu 📬</h2>
    <p class="card-description">
        Terima kasih sudah mendaftar! Sebelum memulai, mohon verifikasi alamat emailmu melalui tautan
        yang baru kami kirimkan.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="success-note">Tautan verifikasi baru sudah dikirim ke alamat emailmu.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="submit-button" type="submit">Kirim ulang email verifikasi</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="text-button back-button" type="submit">Keluar</button>
    </form>
</div>
@endsection
