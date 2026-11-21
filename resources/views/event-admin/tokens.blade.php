@extends('layouts.roborace-event-admin')

@section('title', 'Token Perangkat Arena')
@php($page = 'tokens')
@section('hero-title', 'Waktu resmi dari lintasan.')
@section('hero-subtitle', 'Kelola token yang dipakai perangkat arena untuk mengirim start, lap, dan finish.')
@section('hero-mascot', 'd8b40.png')

@section('content')
@if (session('status'))
    <div class="notice success">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="notice warning">
        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

@if ($plainToken)
    <section class="card token-once">
        <h2>Salin token sekarang</h2>
        <p class="muted">
            Token untuk <strong>{{ $plainArena }}</strong> hanya ditampilkan sekali.
            Simpan di perangkat arena; bila hilang, terbitkan token baru (token lama otomatis tidak berlaku).
        </p>
        <pre class="token-value"><code>{{ $plainToken }}</code></pre>
        <div class="actions">
            <button class="button primary" type="button" data-copy-token>Salin token</button>
            <span class="muted">Tempel sebagai <code>Authorization: Bearer …</code>.</span>
        </div>
    </section>
@endif

<section class="card">
    <h2>Token per arena</h2>
    <p class="muted">
        Setiap arena memakai akun layanan khusus dan satu token berizin <code>arena:timing</code>.
        Token hanya bisa mengirim waktu untuk arena pemiliknya, dan bisa dicabut kapan saja.
    </p>

    <div class="rows">
        @forelse ($arenas as $arena)
            @php($token = $arena->apiUser?->tokens->first())
            <div class="row">
                <div class="row-copy">
                    <strong>{{ $arena->name }} · {{ $arena->code ?: 'TANPA KODE' }}</strong>
                    <span>
                        @if ($token)
                            Terbit {{ $arena->token_issued_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                            · Terakhir dipakai {{ $token->last_used_at?->diffForHumans() ?? 'belum pernah' }}
                        @else
                            Belum ada token aktif
                        @endif
                    </span>
                    <span>
                        Status arena: {{ $arena->is_active ? 'Aktif' : 'Nonaktif' }}
                        @if ($arena->apiUser)
                            · Akun: {{ $arena->apiUser->email }}
                        @endif
                    </span>
                </div>
                <div class="row-actions">
                    <span class="badge {{ $token ? 'badge-aktif' : 'badge-selesai' }}">
                        {{ $token ? 'Token aktif' : 'Tanpa token' }}
                    </span>
                    <form method="POST" action="{{ route('eventadmin.tokens.store', $arena) }}"
                          onsubmit="return confirm('{{ $token ? 'Terbitkan token baru? Token lama langsung tidak berlaku.' : 'Terbitkan token untuk arena ini?' }}')">
                        @csrf
                        <button class="button yellow" type="submit">{{ $token ? 'Putar token' : 'Terbitkan token' }}</button>
                    </form>
                    @if ($token)
                        <form method="POST" action="{{ route('eventadmin.tokens.destroy', $arena) }}"
                              onsubmit="return confirm('Cabut token arena ini? Perangkat tidak bisa mengirim waktu lagi.')">
                            @csrf
                            @method('DELETE')
                            <button class="chip" type="submit">Cabut</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="muted">Belum ada arena pada event ini. Tambahkan arena di menu Jadwal &amp; Arena.</p>
        @endforelse
    </div>
</section>

<section class="card">
    <h2>Cara perangkat mengirim waktu</h2>
    <p class="muted">
        Semua waktu dalam <strong>milidetik sejak start run</strong> (monoton), bukan jam dinding —
        jadi selisih jam perangkat dan server tidak merusak hasil. Pengiriman ulang aman:
        bacaan pertama yang disimpan, dan lap di-upsert berdasarkan nomor lap.
    </p>

    <pre class="token-value"><code># 1. Run apa yang sedang berjalan di arena ini?
curl -H "Authorization: Bearer TOKEN" \
  {{ $apiBase }}/v1/arena/current-run

# 2. Mulai
curl -X POST -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  -d "started_ms=0" \
  {{ $apiBase }}/v1/runs/RUN_UUID/start

# 3. Lap (boleh berkali-kali, lap_no opsional)
curl -X POST -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  -d "laps[0][lap_no]=1" -d "laps[0][lap_ms]=18450" \
  {{ $apiBase }}/v1/runs/RUN_UUID/laps

# 4. Selesai — finished_ms adalah total waktu tempuh
curl -X POST -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  -d "finished_ms=55210" \
  {{ $apiBase }}/v1/runs/RUN_UUID/finish</code></pre>

    <div class="notice warning">
        Waktu dari arena dipakai untuk pemecah seri, dan menjadi nilai resmi pada kategori
        dengan mode penilaian <strong>Waktu</strong>. Kategori mode <strong>Juri</strong> tetap
        memakai skor juri; waktu hanya menjadi pemecah seri.
    </div>
</section>

<div class="mascot-note">
    <img src="{{ asset('assets/17d7a.png') }}" alt="">
    <strong>Setiap penerbitan dan pencabutan token tercatat di audit log. Jangan bagikan token di grup publik.</strong>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('[data-copy-token]')?.addEventListener('click', async (event) => {
        const value = document.querySelector('.token-value code')?.textContent?.trim();
        if (!value) return;
        try {
            await navigator.clipboard.writeText(value);
            event.currentTarget.textContent = 'Tersalin!';
        } catch {
            event.currentTarget.textContent = 'Salin manual';
        }
    });
</script>
@endpush
