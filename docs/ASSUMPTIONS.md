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
| A14 | Koreksi skor resmi hanya atas skor **Approved** (frozen); dicatat sebagai keputusan `corrected` append-only dengan alasan wajib | Chief tak boleh mengedit bebas; hanya jalur koreksi resmi yang menghasilkan jejak audit. |
| A15 | Export CSV hanya untuk kategori **Published** (klasemen) | Mencegah kebocoran hasil draft/sepihak. Rekap partisipasi boleh seluruh tim karena bukan nilai. |
| A16 | Sertifikat dirender sebagai **halaman HTML siap cetak (print-to-PDF)**, bukan PDF biner | Menghindari dependensi PDF baru; bisa dicetak/di-PDF via browser; verifikasi publik lewat token QR (UUID) `. |
| A17 | Banding: coach mengajukan per run, Chief menyelesaikan (accept/reject/review) dengan catatan wajib | Sesuai tabel `appeals`; satu run = satu banding terbuka pada satu waktu. |
| A18 | Realtime memakai strategi **reload on event** (bukan patch DOM in-place) di kanal publik (`event.*`, `category.*`) & privat (`arena.*`) | Server-rendered standings tetap sumber kebenaran tunggal; menghindari drift. Kanal privat arena dibatasi staf yang ditugaskan. |
| A19 | Klien realtime hanya memuat websocket bila halaman menyertakan meta live (`live-event`/`live-category`/`live-arena`) | Halaman statis tidak membuka koneksi; hemat resource. |
| A20 | Registrasi mandiri (`/register`) membuat akun berperan **Coach** sekaligus `School` + `Coach` profile; sekolah yang sudah ada (nama sama) dipakai ulang | Tanpa tautan sekolah, dashboard coach kosong. Pendaftaran tim/peserta tetap dilakukan panitia/coach setelah verifikasi. |
| A21 | Peran **Publik** punya dashboard sendiri (`/publik/dasbor`) di layout RoboRace, bukan halaman Breeze lama | Menyatukan desain 8 aplikasi front-end; halaman ini jadi tujuan default akun tanpa peran kompetisi. |
| A22 | **Menu akun** bersama (`<details>` tanpa JS) dimuat di 6 layout peran lewat entry Vite terpisah `account.css` | Satu komponen untuk Dasbor/Panel panitia/Profil/Keluar; stylesheet terpisah agar tidak bentrok dengan CSS tiap role. |
| A23 | Coach mendaftarkan tim + peserta sendiri (satu transaksi: `Team` + `Participant` + `Registration` draft) | Inti Fase 3; ukuran tim divalidasi terhadap `min/max_team_members`, NISN dicek lewat blind index, tim hanya bisa diedit selama `Draft`/`Rejected`. |
| A24 | Event Admin menugaskan operator/juri lewat `/event-admin/staff` (bukan hanya Filament) | Penugasan menentukan arena yang bisa dikontrol/dinilai; role penugasan `operator` dipetakan ke peran platform `arena_operator`. |
| A25 | Pengaturan `require_email_verification` benar-benar ditegakkan middleware `verified.setting` | Sebelumnya hanya tersimpan. Baris setting yang belum ada dianggap "belum dikonfigurasi" (permissive) agar instalasi baru tetap bisa dipakai; akun demo di-seed terverifikasi. |
| A26 | Penjadwalan: sesi bisa **diubah** (arena/waktu) dan **dibatalkan** selama masih `scheduled` dan belum punya skor | Operator butuh antrean yang stabil; slot heat yang kosong ikut dihapus. Slot unik per arena (bukan per kategori). |
| A27 | Super Admin dapat **mengubah peran** akun (satu peran aktif) dan promosi ke Coach membuat `School` + profil `Coach` bila belum ada | Tanpa ini, koreksi peran hanya bisa lewat DB. Akun sendiri tidak bisa diubah/dinonaktifkan. |
| A28 | Event Admin hanya dapat mengubah status kategori sampai **Semua Run Selesai**; `Published` khusus Chief Judge | Menjaga pemisahan tugas: hasil resmi hanya terbit setelah pengesahan skor. |
| A29 | API waktu arena memakai **Sanctum bearer token per arena** (bukan API key global/HMAC) | Token bisa dicabut/rotasi per arena, punya ability `arena:timing`, dan terikat ke `arenas.api_user_id` sehingga satu perangkat tidak bisa menyentuh arena lain. |
| A30 | Waktu dikirim sebagai **milidetik sejak start** (monoton), bukan timestamp jam dinding | Menghindari hasil rusak akibat jam perangkat tidak sinkron dengan server. Bacaan pertama menang; lap di-upsert per `lap_no` (idempotent). |
| A31 | Kategori punya **mode penilaian** `jury`/`time` (`categories.scoring_mode`) | User memutuskan waktu arena bisa jadi pemecah seri *dan* nilai akhir pada sebagian lomba. Mode `time` memakai `official_time_ms` tercepat; mode `jury` tetap memakai skor juri. |
| A32 | Agregasi juri mengikuti rulebook (`aggregation.judges`: average/best/total, `aggregation.attempt`: best/total/average) | Sebelumnya `StandingsService` mengambil `max()` dari semua skor approved — kalau 3 juri menilai run yang sama, nilai tertinggi yang dipakai. Itu melanggar rulebook. |
| A33 | Tie-break memakai `rulebook.tie_break_order` (default `primary_achievement > penalty > time > extra_run`) | Sebelumnya tersimpan tapi tidak pernah dipakai; seri ditentukan urutan acak DB. Fallback terakhir: nama tim, supaya urutan tidak berubah antar request. |
| A34 | Mode penilaian **terkunci setelah kategori dipublikasikan** | Mengubah mode setelah publikasi akan menulis ulang podium resmi; perubahan hanya lewat jalur koreksi resmi. |
| A35 | API arena memakai `auth:sanctum` + penolakan token non-`PersonalAccessToken` | Mencegah sesi browser staf dipakai sebagai "perangkat arena"; endpoint hanya menerima bearer token sungguhan. |
| A36 | Waktu resmi hanya berlaku untuk run berstatus **`finished`** | Perangkat arena bisa saja menghentikan stopwatch pada run yang kemudian didiskualifikasi. Bacaan waktu pada run `dq`/`dnf`/`no_show` diabaikan: tidak masuk klasemen mode `time`, tidak dipakai tie-break, dan tidak dianggap hasil publik (`hasApprovedScores`). Run non-`finished` tidak memblokir publikasi karena statusnya sudah final tanpa waktu. |

## Sudah diselesaikan (sebelumnya terbuka)

- **Ambang Publish** (butir 4 lama): prasyarat pemblokiran sudah final — semua run *settled* + semua skor `Approved` + setiap run selesai punya skor. Ditampilkan sebagai daftar alasan di halaman publikasi.
- **Evidence vault**: unggah multi-berkas oleh juri, disimpan di disk privat, disajikan lewat rute ber-otorisasi (`/bukti/{uuid}`) untuk staf arena, coach terkait, dan pengunggah.
- **Laporan insiden**: operator mencatat & memperbarui insiden pada arena yang ditugaskan.
- **Export CSV**: klasemen (published-only) & rekap partisipasi, UTF-8 BOM.
- **Realtime**: Reverb + laravel-echo/pusher-js; event `RunStatusChanged` & `StandingsUpdated`.

## Butuh keputusan pengguna (belum ditebak)

1. **2FA** untuk Super Admin/Event Admin — belum diimplementasikan. Fortify diperlukan bila wajib. (Fase 2/7)
2. **Provider S3** — config `filesystems` sudah S3-ready via env, belum ada bucket nyata. (Fase 5)
3. **Mekanisme consent orang tua** (unggah scan vs checkbox digital) — model sudah punya `consent_parent` + `consent_media` + `consent_at`, alur detail di Fase 3.
4. **UAT & load test** (Fase 7) masih manual — belum ada skrip otomatis.
