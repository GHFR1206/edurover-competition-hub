# NanoRobotic Competition Hub

Sistem manajemen kompetisi robot **EduRover** untuk jenjang **SD, SMP, SMA** — multi-arena paralel,
penilaian juri berbasis poin, verifikasi/approval/publikasi oleh Chief Judge, evidence vault,
audit trail, dan export untuk kurasi.

> Status: **Fase 0 — Fondasi teknis** selesai. Lihat `docs/ASSUMPTIONS.md` dan rencana fase di bawah.

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
| 1 | UX: user journey, storyboard, prototype Blade diklik (data dummy) | ⏳ menunggu |
| 2 | Auth, 7 peran, event, kategori, tantangan dinamis, rulebook config | — |
| 3 | Sekolah, tim, peserta, registrasi, verifikasi, consent | — |
| 4 | Multi-arena, jadwal, antrean, run lifecycle, penugasan operator/juri | — |
| 5 | Scoring engine, judge entry, evidence upload, approve/reopen, audit | — |
| 6 | Realtime privat, visibilitas guest, Publish, leaderboard, export | — |
| 7 | Hardening: security, visibilitas, offline/retry, load test, UAT | — |

## CI

`.github/workflows/ci.yml` menjalankan **Pint** (style) dan **Pest** (test) pada setiap push/PR.
