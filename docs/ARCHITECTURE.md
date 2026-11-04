# ARSITEKTUR — NanoRobotic Competition Hub

Sistem manajemen kompetisi robot EduRover (SD/SMP/SMA) dengan multi-arena paralel,
penilaian juri berbasis poin, verifikasi/approval/publikasi Chief Judge, evidence vault,
audit trail, dan export kurasi.

## Stack

| Lapis | Teknologi |
|---|---|
| Framework | Laravel 12, PHP 8.3 |
| Database | MySQL 8.0 |
| Auth | Laravel Breeze (session) + spatie/laravel-permission |
| Panel staff | Filament v3 (Event Admin, Operator Arena, Chief Judge, Reporting) |
| Halaman juri | Livewire 3, tablet-first (terpisah dari Filament) |
| Publik & tim | Blade + Livewire 3 + Tailwind |
| Realtime | Laravel Reverb + Echo (PRIVATE channel `team.{id}`) |
| Queue | database (redis-ready) |
| Storage | filesystem disk (S3-ready) |
| Test | Pest 4 |
| Style | Pint |

## Struktur folder

```
app/
├── Domain/
│   └── Scoring/            ScoringEngine (pure), ScoreLineInput, ScoreResult
├── Enums/                  Role, Level, RunStatus, ScoreStatus, CategoryStatus,
│                           RegistrationStatus, ScoreObjectType
├── Models/                 Seluruh Eloquent model + Concerns (HasUuid, Auditable)
├── Policies/               ScorePolicy, RunPolicy, EventPolicy
├── Providers/Filament/     AdminPanelProvider
database/
├── migrations/             Skema domain lengkap
├── factories/              Factory tiap model
└── seeders/                RolesAndPermissionsSeeder, DemoSeeder, ExampleChallengeData
docs/
├── ASSUMPTIONS.md
├── ARCHITECTURE.md
└── ux/                     (Fase 1)
docker/
└── php/                    Dockerfile + php.ini
```

## Domain model

- **Competition**: `events` → `categories` (1 per jenjang) → `rulebooks` (versioned, `locked_at`)
  - `challenges` (per kategori) → `score_objects` (objek/checkpoint/pencapaian/penalti)
  - `capability_tags` (kemampuan dasar per jenjang)
- **Registration**: `schools` → `coaches`, `teams` → `participants` (NISN encrypted), `registrations`
- **Operations**: `arenas` → `arena_assignments`, `heat_slots`, `runs`
- **Scoring**: `scores` (versioned, `ruleset_snapshot`) → `score_lines`
- **Approvals**: `result_approvals`, `appeals`, `category_publications`
- **Evidence & audit**: `evidence_files`, `incident_reports`, `audit_logs` (append-only), `certificates`
- **Telemetry**: `telemetry_events` (data pendukung, tidak masuk skor)

## Status

| Entitas | Alur |
|---|---|
| Run | Scheduled → Called → Ready → Running → Finished (+ DNF / DQ / no-show / re-run) |
| Score | Draft → Submitted → Approved (frozen) → Reopened → versi baru |
| Category | Registration → Scheduled → Running → AllRunsFinished → Published |

## Prinsip keamanan

1. **Least privilege** — permission granular, Policies per model.
2. **Maker-checker** — pengisi skor ≠ pengesah; `ScorePolicy::approve` menolak bila `judge_id === user->id`.
3. **Immutable** — skor `Approved` tak bisa diubah; koreksi lewat "reopen resmi" dengan alasan wajib.
4. **PII** — NISN encrypted + hidden, tidak muncul di audit/export publik.
5. **Visibilitas hasil** — diterapkan di Fase 6 (private channel, guest hanya pasca-Publish).
6. **Telemetry** — di balik flag `FEATURE_TELEMETRY` (default OFF), tidak mempengaruhi nilai resmi.

## Menjalankan

Lihat `README.md`.
