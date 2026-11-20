# Serah-terima ke tim backend

## Peran dan akses
| Role | App | Bisa |
|---|---|---|
| Publik (tanpa login) | publik | Lihat event, jadwal, leaderboard, hasil resmi, arsip |
| coach | coach | Buat tim, anggota, lihat robot_code/API key (setelah verified), hasil, jadwal |
| event_admin | event-admin | Event, setup (kategori, ruleset, challenge), staf, jadwal, laporan |
| operator_arena | operator | Antrean sesi, status sesi, penanganan khusus, re-run |
| juri | juri | Suara pencapaian (remote), nilai manual (draft/submit) |
| chief_judge | chief-judge | Putuskan pencapaian/nilai, publikasi level, koreksi resmi |
| super_admin | super-admin | Verifikasi tim, pengguna, audit log, pengaturan |

## Endpoint yang dibutuhkan (usulan, REST)
- Auth: `POST /auth/login`, `/auth/register`, `/auth/forgot`, `/auth/reset`, `GET /me` (sesi + role; frontend mengarahkan per role)
- Event: `GET/POST /events`, `POST /events/:id/advance` (draft→open_regis→ongoing→finished, tolak jika setup belum lengkap), `GET/PUT /events/:id/setup`
- Tim: `GET/POST /teams`, `PATCH /teams/:id`, `POST /teams/:id/members`, `POST /teams/:id/verify|reject|request-revision` (alasan wajib untuk reject/revision), `GET /teams/:id/robot`
- Sesi: `GET/POST /sessions` (409 jika bentrok arena atau tim), `POST /sessions/:id/transition` (call, check-in, start, finish, no_show, dnf, dq, disrupted), `POST /sessions/:id/rerun`
- Pencapaian: `POST /robot/achievements` (auth API key robot, TIDAK menentukan skor resmi), `PUT /achievements/:id/vote` (juri, boleh diubah sebelum final), `POST /achievements/:id/decision` (chief; alasan wajib untuk reject/re_review)
- Nilai manual: `PUT /sessions/:id/manual-score` (draft), `POST .../submit` (kunci), `POST /manual-scores/:id/corrections` (chief; alasan wajib, riwayat dipertahankan)
- Hasil: `GET /leaderboard?level=`, `POST /events/:id/levels/:level/publish` (hanya jika semua tim valid), `GET /results?level=`, `GET /archive?year=`, `GET /reports/*.csv|pdf`
- Admin: `GET/POST/PATCH /users`, `GET /audit-logs?type=&q=&page=`, `GET/PUT /settings`
- Realtime: leaderboard, antrean arena, dan suara juri (WebSocket/SSE).

## Aturan bisnis (server = sumber kebenaran)
1. Total = Σ poin pencapaian `approved` + rata-rata nilai manual (lihat `totalScore` di contracts).
2. Kuorum = lebih dari separuh juri panel. Mayoritas hanya "menunggu Chief"; poin resmi hanya setelah keputusan Chief.
3. `official` hanya setelah level dipublikasikan; sebelumnya `provisional` atau `valid`.
4. Transisi status event satu arah. Jadwal tidak boleh bentrok per arena dan per tim.
5. Setiap aksi penting wajib masuk audit log (pelaku, objek, sebelum/sesudah, waktu).
6. Data dari robot hanya data pendukung, tidak pernah menjadi skor resmi.

## Keputusan produk yang masih terbuka (jangan diasumsikan backend)
1. **Dua model penilaian manual.** Halaman Nilai Manual memuat nilai lama 0–20 dan parameter baru (Task 60 / Navigasi 25 / Efisiensi 15 + bonus/penalty). Pilih satu.
2. **Ukuran panel juri.** Event Admin memakai 4 juri per level, tetapi popup remote Juri menampilkan 3 juri ("lebih dari setengah dari 3 juri").
3. **Data contoh bertentangan.** ORION: Publik skor 106 Resmi, Chief Judge skor 40 Sementara. Seed harus dibuat dari satu sumber.
4. **Aturan "Valid vs Resmi"** untuk level yang sebagian timnya tidak memenuhi syarat (contoh PIXEL).

## Sudah dikerjakan
- Satu repo, `package.json` bersih, tanpa plugin atau berkas Figma Make; font memakai Google Fonts (self-host untuk produksi).
- Tipe dan enum bersama di `packages/contracts` (belum dipakai di app).
- Kata sandi login tidak lagi terisi otomatis.

## Belum dikerjakan (tahap berikutnya)
- Mengganti `data.ts` dengan lapisan API (`src/api/*`) dan memakai tipe dari contracts (hapus tipe duplikat; skor sebagai number; tim dikenali lewat `id`).
- Router dan URL per halaman, guard per role, shell dan komponen bersama (Navbar, Footer, Maskot) yang masih ditulis ulang di tiap app; CSS global tiap app bertabrakan bila digabung.
- Menggabungkan status berupa teks tampilan (`"3 setuju · 1 tolak"`) menjadi angka + enum.
- Tes otomatis, type-check penuh (`npm run build` setelah `npm install`), dan pengujian di browser.
