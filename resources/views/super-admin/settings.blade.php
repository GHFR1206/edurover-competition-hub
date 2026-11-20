@extends('layouts.roborace-super-admin')

@section('title', 'Pengaturan')
@php($page = 'pengaturan')

@section('content')
<div class="content decorated settings">
    <form method="POST" action="{{ route('superadmin.settings.update') }}">
        @csrf
        @method('PATCH')

        <section class="card">
            <h2>Keamanan akun</h2>
            <div class="setting-row">
                <div>
                    <strong>Wajibkan verifikasi email</strong>
                    <p>Akun baru harus memverifikasi email sebelum mengirim data tim untuk ditinjau.</p>
                    <span class="status {{ $settings['require_email_verification'] ? 'green' : 'red' }}">
                        {{ $settings['require_email_verification'] ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <label class="toggle {{ $settings['require_email_verification'] ? 'on' : '' }}">
                    <input type="checkbox" name="require_email_verification" value="1" hidden
                           @checked($settings['require_email_verification'])>
                    <i></i>
                </label>
            </div>
        </section>

        <section class="card">
            <h2>Metode penilaian default</h2>
            <div class="chips">
                @foreach (['Manual', 'Hybrid', 'Telemetri'] as $method)
                    <label class="button {{ $settings['scoring_method'] === $method ? 'primary' : '' }}">
                        <input type="radio" name="scoring_method" value="{{ $method }}" hidden @checked($settings['scoring_method'] === $method)>
                        {{ $method }}
                    </label>
                @endforeach
            </div>
            <p>Hybrid menggabungkan pencapaian robot dengan rata-rata nilai manual juri. Semua pencapaian tetap memerlukan keputusan Chief Judge.</p>
            <small>Manual: nilai diisi juri · Telemetri: pencapaian dikirim robot · Hybrid: keduanya digunakan bersama.</small>
            <hr>
            <label>Nama layanan robot
                <input type="text" name="robot_service_name" value="{{ $settings['robot_service_name'] }}">
            </label>
            <small>START 0 · CP1/CP2/CP3 masing-masing 20 · FINISH 40 · rata-rata manual 0–20.</small>
        </section>

        <div class="button-row">
            <button class="button yellow" type="submit">Simpan pengaturan</button>
            <a class="button" href="{{ route('superadmin.settings.index') }}">Batalkan perubahan</a>
        </div>
    </form>

    <div class="mascot-note">
        <img src="{{ asset('assets/17d7a.png') }}" alt="">
        <strong>Pengaturan default berlaku untuk event baru. Ruleset event berjalan tetap terkunci agar kompetisi adil.</strong>
    </div>
</div>
@endsection
