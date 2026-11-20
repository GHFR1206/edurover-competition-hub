<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil Akun · RoboRace</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Lilita+One&family=Nunito:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap">
    @vite(['resources/css/roborace/public.css'])
</head>
<body>
<div class="profile-page">
    <header class="profile-hero">
        <a class="brand" href="{{ route('dashboard') }}">Robo<span>Race</span></a>
        <div>
            <span class="eyebrow">AKUN</span>
            <h1>Profil &amp; Keamanan</h1>
            <p class="muted">Perbarui data akun, kata sandi, dan kelola sesi Anda.</p>
        </div>
        <a class="outline-btn" href="{{ route('dashboard') }}">← Kembali ke dasbor</a>
    </header>

    @if (session('status') === 'profile-updated')
        <div class="notice">✓ Profil tersimpan.</div>
    @endif

    <main class="profile-grid">
        <section class="card">
            <span class="eyebrow">DATA DIRI</span>
            <h2>Informasi profil</h2>
            <p class="muted">Perbarui nama dan alamat email akun Anda.</p>

            @if ($errors->get('name') || $errors->get('email'))
                <div class="notice warning">
                    @foreach (array_merge($errors->get('name'), $errors->get('email')) as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                @csrf
                @method('patch')

                <label class="field">
                    <span>Nama</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                </label>

                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                </label>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="notice warning">
                        Email belum diverifikasi.

                        <form method="POST" action="{{ route('verification.send') }}" style="display:inline">
                            @csrf
                            <button type="submit" class="link-btn">Kirim ulang tautan verifikasi</button>
                        </form>

                        @if (session('status') === 'verification-link-sent')
                            <div>✓ Tautan verifikasi baru telah dikirim.</div>
                        @endif
                    </div>
                @endif

                <div class="actions">
                    <button class="purple-btn" type="submit">Simpan profil</button>
                </div>
            </form>
        </section>

        <section class="card">
            <span class="eyebrow">KEAMANAN</span>
            <h2>Ubah kata sandi</h2>
            <p class="muted">Gunakan kata sandi yang panjang dan acak agar akun aman.</p>

            @if ($errors->updatePassword->any())
                <div class="notice warning">
                    @foreach ($errors->updatePassword->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="profile-form">
                @csrf
                @method('put')

                <label class="field">
                    <span>Kata sandi saat ini</span>
                    <input type="password" name="current_password" autocomplete="current-password">
                </label>

                <label class="field">
                    <span>Kata sandi baru</span>
                    <input type="password" name="password" autocomplete="new-password">
                </label>

                <label class="field">
                    <span>Konfirmasi kata sandi baru</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password">
                </label>

                <div class="actions">
                    <button class="purple-btn" type="submit">Perbarui kata sandi</button>
                </div>
            </form>
        </section>

        <section class="card danger-card">
            <span class="eyebrow">ZONA BERBAHAYA</span>
            <h2>Hapus akun</h2>
            <p class="muted">Setelah akun dihapus, seluruh data di dalamnya tidak dapat dipulihkan. Konfirmasi dengan kata sandi Anda.</p>

            @if ($errors->userDeletion->any())
                <div class="notice warning">
                    @foreach ($errors->userDeletion->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.destroy') }}" class="profile-form"
                  onsubmit="return confirm('Yakin ingin menghapus akun ini? Tindakan ini permanen.')">
                @csrf
                @method('delete')

                <label class="field">
                    <span>Kata sandi</span>
                    <input type="password" name="password" placeholder="Masukkan kata sandi untuk konfirmasi">
                </label>

                <div class="actions">
                    <button class="danger-btn" type="submit">Hapus akun</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
