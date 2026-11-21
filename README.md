# NanoRobotic Competition Hub

Sistem manajemen kompetisi robot **EduRover** untuk jenjang **SD, SMP, SMA** — multi-arena paralel,
penilaian juri berbasis poin, verifikasi/approval/publikasi oleh Chief Judge, evidence vault,
audit trail, dan export untuk kurasi.

> Status: **Fase 0–7 selesai** (fungsional). Fitur inti lengkap: scoring, evidence vault, approval &
> publikasi, koreksi resmi, banding, laporan insiden, sertifikat, export CSV, dan realtime. Lihat
> `docs/ASSUMPTIONS.md` dan peta fase di bawah.

## Stack

Laravel 12 · PHP 8.3 · MySQL 8.0 · Filament v3 · Livewire 3 · Laravel Reverb · spatie/laravel-permission ·
Pest 4 · Pint · Tailwind CSS 4

## Prasyarat

- Docker + Docker Compose (host tidak perlu PHP/Node/MySQL).
- Port default: app `8010`, MySQL `3310`, Reverb `8090`.

## Setup (Docker)

```bash
# 1. Siapkan env
cp .env.example .env

# 2. Build image & jalankan service
docker compose up -d --build

# 3. Generate app key (sekali)
docker compose exec app php artisan key:generate

# 4. Migrasi + seed demo
docker compose exec app php artisan migrate:fresh --seed

# 5. Build aset front-end (dijalankan lewat kontainer node)
docker run --rm -v "$PWD":/app -w /app node:24-alpine sh -c "npm install && npm run build"
```

Akses:

- App/publik: http://localhost:8010
- Panel staff (Filament): http://localhost:8010/admin
- Reverb WebSocket: ws://localhost:8090

### Akun demo (password semua: `password`)

| Peran | Email |
|---|---|
| Super Admin | admin@nanorobotic.test |
| Event Admin | eventadmin@nanorobotic.test |
| Operator Arena | operator@nanorobotic.test |
| Juri | juri@nanorobotic.test |
| Chief Judge | chief@nanorobotic.test |
| Coach / Sekolah | coach@nanorobotic.test |
| Publik | publik@nanorobotic.test |

> Pendaftaran mandiri lewat `/register` membuat akun **Coach** beserta profil sekolahnya, lalu
> diarahkan ke dasbor coach. Coach dapat membuat tim + anggota sendiri di `/coach/tim/baru`,
> mengajukan verifikasi, dan memantau statusnya. Akun **Publik** (penonton) mendapat dasbor sendiri
> di `/publik/dasbor`.

## Perintah harian

Semua perintah PHP dijalankan di dalam kontainer:

```bash
docker compose exec app php artisan <cmd>
docker compose exec app ./vendor/bin/pint          # format
docker compose exec app php artisan test           # Pest
docker compose exec app ./vendor/bin/pest -p      # test paralel (opsional)
```

Tanpa `docker compose exec` (kontainer sekali pakai):

```bash
docker run --rm --entrypoint sh -v "$PWD":/var/www/html -w /var/www/html \
  --network nanorobotic-competition-hub_nanorobotic nanorobotic-app:latest \
  -c 'php artisan test'
```

## Data demo

`DemoSeeder` mengisi: 1 event, 3 kategori (SD/SMP/SMA), 24 tim (8/jenjang), 4 arena, 6 run contoh,
user tiap peran + 3 juri tambahan. **Score object yang diseed adalah CONTOH, bukan final rulebook.**

## Struktur dokumentasi

- `docs/ARCHITECTURE.md` — arsitektur & domain model.
- `docs/ASSUMPTIONS.md` — asumsi & deviasi dari brief.
- `docs/ux/` — user journey, storyboard, prototype (Fase 1).

## Peta fase

| Fase | Isi | Status |
|---|---|---|
| 0 | Fondasi teknis (deps, struktur domain, README, CI) | ✅ selesai |
| 1 | UX: user journey, storyboard, prototype Blade diklik (data dummy) | ✅ selesai |
| 2 | Auth, 7 peran, event, kategori, tantangan dinamis, rulebook config | ✅ selesai |
| 3 | Sekolah, tim, peserta, registrasi, verifikasi, consent | ✅ selesai |
| 4 | Multi-arena, jadwal, antrean, run lifecycle, penugasan operator/juri | ✅ selesai |
| 5 | Scoring engine, judge entry, evidence upload, approve/reopen, audit | ✅ selesai |
| 6 | Realtime privat, visibilitas guest, Publish, leaderboard, export | ✅ selesai |
| 7 | Hardening: security, visibilitas, offline/retry, load test, UAT | ⏳ sebagian (UAT/load test manual) |

### Halaman per peran

| Peran | Dasbor | Area |
|---|---|---|
| Super Admin | `/super-admin/dashboard` | `/super-admin/*`, panel `/admin` |
| Event Admin | `/event-admin/dashboard` | `/event-admin/*` |
| Operator Arena | `/operator/dashboard` | `/operator/*` |
| Juri | `/juri/dashboard` | `/juri/*` |
| Chief Judge | `/chief/dashboard` | `/chief/*` |
| Coach / Sekolah | `/coach/dashboard` | `/coach/*` |
| Publik | `/publik/dasbor` | halaman publik (`/event`, `/jadwal`, `/leaderboard`, `/hasil`, `/arsip`) |

`/dashboard` adalah pintu masuk tunggal setelah login dan mengarahkan tiap peran ke dasbornya.
Semua layout peran punya menu akun bersama (Dasbor / Panel panitia untuk staf / Profil & keamanan /
Keluar) di kanan navbar.

### Operasional yang bisa dilakukan langsung dari aplikasi

| Peran | Aksi |
|---|---|
| Coach | Daftarkan tim + anggota (`/coach/tim/baru`), tambah/hapus anggota, ajukan verifikasi dengan consent, ajukan banding |
| Event Admin | Buat event & maju tahap, atur status kategori (sampai *semua run selesai*), pilih mode penilaian kategori (juri/waktu), tugaskan/nonaktifkan operator & juri per arena, terbitkan/putar/cabut token perangkat arena, jadwalkan/ubah/batalkan sesi run |
| Super Admin | Verifikasi registrasi, undang pengguna, ubah peran (termasuk promosi ke Coach + sekolah), aktif/nonaktifkan akun, pengaturan platform |
| Chief Judge | Tinjau/approve/reject/reopen skor, koreksi resmi skor beku, selesaikan banding, publikasi per kategori, terbitkan sertifikat |

Publishing kategori sengaja hanya bisa dilakukan Chief Judge; Event Admin dibatasi sampai status
*Semua Run Selesai* agar hasil tidak terbit sebelum disahkan.

## API waktu arena (v1)

Perangkat arena (timer/robot) mengirim **start, lap, dan finish** lewat API ber-token. Token
dikelola per arena oleh Event Admin di `/event-admin/token-arena`.

| Endpoint | Kegunaan |
|---|---|
| `GET /api/v1/arena/current-run` | Run yang sedang/akan berjalan di arena pemilik token |
| `POST /api/v1/runs/{uuid}/start` | Sinyal start (`started_ms`, default 0) |
| `POST /api/v1/runs/{uuid}/laps` | Satu atau banyak split (`laps[][lap_no]`, `laps[][lap_ms]`) |
| `POST /api/v1/runs/{uuid}/finish` | Sinyal finish (`finished_ms` = total waktu tempuh) |
| `POST /api/v1/runs/{uuid}/timing` | Payload gabungan start + lap + finish |
| `GET /api/v1/runs/{uuid}/timing` | Baca ulang waktu tersimpan (rekonsiliasi perangkat) |

Aturan yang berlaku:

- **Autentikasi**: `Authorization: Bearer <token>` (Sanctum). Token milik akun layanan arena,
  hanya berizin `arena:timing`, dan hanya bisa menyentuh run di arena pemiliknya. Rotasi token
  langsung membatalkan token lama; pencabutan berlaku seketika.
- **Satuan**: semua waktu **milidetik sejak start run** (monoton), bukan jam dinding. Selisih jam
  perangkat vs server tidak mempengaruhi hasil.
- **Idempotent**: bacaan pertama yang disimpan; lap di-upsert berdasarkan `lap_no`, jadi perangkat
  yang mengulang kiriman setelah putus jaringan tidak merusak hasil. Respons memuat `first_seen`.
- **Rate limit**: 120 permintaan/menit per token.
- **Efek**: `official_time_ms`, `lap_count`, `duration_seconds` terisi; run otomatis menjadi
  `running` saat start dan `finished` saat finish. Status `dq`/`no_show` tidak pernah ditimpa.
- **Telemetri mentah** tetap di balik `FEATURE_TELEMETRY` (default OFF). Waktu resmi tetap dicatat
  walau flag telemetri mati.

### Mode penilaian kategori

| Mode | Nilai resmi | Tie-break |
|---|---|---|
| `jury` (default) | Skor juri yang `Approved`, agregasi sesuai `aggregation.judges` (average/best/total) lalu `aggregation.attempt` | `tie_break_order` rulebook (default achievement → penalti → waktu → run tambahan) |
| `time` | `official_time_ms` tercepat dari run yang *settled* | Waktu tercepat |

Mode dipilih Event Admin di `/event-admin/setup` dan **terkunci setelah kategori dipublikasikan**,
karena mengubahnya akan menulis ulang podium resmi.

## CI

`.github/workflows/ci.yml` menjalankan **Pint** (style) dan **Pest** (test) pada setiap push/PR.
