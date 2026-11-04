# ASSUMPTIONS

Catatan asumsi & deviasi dari brief. Setiap butir yang tidak eksplisit di brief dicatat di sini
alih-alih ditebak diam-diam.

| # | Asumsi / Deviasi | Alasan |
|---|---|---|
| A1 | Database **MySQL 8.0** (bukan PostgreSQL) | Instruksi revisi pengguna. Linux case-sensitive mode (`lower_case_table_names=0`), semua nama tabel snake_case huruf kecil. |
| A2 | Auth via **Laravel Breeze** (blade stack), bukan Fortify | Breeze menyediakan form login Blade untuk juri/coach; panel Filament punya login sendiri. Lebih ringan. |
| A3 | Test runner **Pest 4** (butuh phpunit ^12.5) | Skrip Laravel 12 membawa phpunit ^11.5 yang bentrok dengan Pest; phpunit dinaikkan ke ^12.5 di `require-dev`. |
| A4 | **Redis tidak dipakai** di dev; queue/cache/session = `database` | Simpel. Bisa dipindah ke redis via env tanpa ubah kode. |
| A5 | Reverb dijalankan sebagai service terpisah (`nanorobotic_reverb`), port host 8090 → 8080 | Dev lokal. |
| A6 | Semua perintah PHP/Composer/jalan server via **Docker** | Host tidak punya PHP/Composer/Node/MySQL native. Kontainer proyek terisolasi (`nanorobotic_*`), tidak menyentuh stack lain. |
| A7 | Seeder demo: 1 event, 3 kategori, 24 tim (8/jenjang), 4 arena, 6 run, 10 user (7 peran + 3 juri tambahan), 3 skor contoh | Sesuai brief, angka 24 tim & 3–4 arena. |
| A8 | NISN dienkripsi via cast `encrypted` + kolom `nisn_hash` (HMAC-SHA256 dgn app key) untuk cek duplikat | Cek unik tanpa dekripsi; NISN di `$hidden`. |
| A9 | Fase 0 hanya **skema + model + factory + policy dasar**; logika bisnis per fase | Brief: "struktur domain" saja di Fase 0. |
| A10 | Scoring engine Fase 0 = **mesin kalkulasi pure + unit test**; integrasi run/DB di Fase 5 | Menghindari fitur dini, tapi sudah teruji unit. |
| A11 | Telemetry flag: `FEATURE_TELEMETRY` (default `false`) via `config/nanorobotic.php` | Brief: default OFF. |
| A12 | Audit log = tabel **append-only**; model `AuditLog` set `UPDATED_AT = null` dan tidak ada endpoint update/delete | Brief: append-only. |
| A13 | `auditAttributes` allowlist per model; NISN sengaja **tidak** masuk snapshot audit | Cegah PII bocor ke audit/export. |

## Butuh keputusan pengguna (belum ditebak)

1. **2FA** untuk Super Admin/Event Admin — belum diimplementasikan. Fortify diperlukan bila wajib. (Fase 2/7)
2. **Provider S3** — config `filesystems` sudah S3-ready via env, belum ada bucket nyata. (Fase 5)
3. **Mekanisme consent orang tua** (unggah scan vs checkbox digital) — model sudah punya `consent_parent` + `consent_media` + `consent_at`, alur detail di Fase 3.
4. **Ambang Publish**: daftar prasyarat pemblokiran final (run selesai + semua Approved) di Fase 6.
