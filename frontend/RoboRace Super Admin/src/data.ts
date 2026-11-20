export type Page = "ringkasan" | "verifikasi" | "pengguna" | "audit" | "pengaturan";

export const navItems: { id: Page; label: string }[] = [
  { id: "ringkasan", label: "Ringkasan" },
  { id: "verifikasi", label: "Verifikasi Tim" },
  { id: "pengguna", label: "Pengguna" },
  { id: "audit", label: "Audit Log" },
  { id: "pengaturan", label: "Pengaturan" },
];

export const events = [
  {
    name: "RoboRace Cup 2026 (Seri Pilot)",
    detail: "14–15 Nov · 36/48 run selesai · 3 arena aktif",
    status: "Ongoing",
    tone: "green",
  },
  {
    name: "RoboRace Regional Seri 2",
    detail: "Buka hingga 30 Des 2026 · 12 tim terdaftar",
    status: "Open Registrasi",
    tone: "yellow",
  },
  {
    name: "Kejuaraan Internal Sekolah",
    detail: "Setup challenge belum lengkap",
    status: "Draft",
    tone: "purple",
  },
  {
    name: "RoboRace Cup 2025 · Final",
    detail: "Selesai · hasil sudah diarsipkan",
    status: "Selesai",
    tone: "purple",
  },
];

export const systems = [
  ["Database", "Koneksi stabil · 12 ms"],
  ["Leaderboard realtime", "Sinkron terakhir 11:35:02"],
  ["API robot EduRover", "3 arena terhubung · 42 ms"],
  ["Backup", "Berhasil · 14 Nov, 03:00 WIB"],
];

export type TeamStatus = "Menunggu" | "Terverifikasi" | "Perlu revisi" | "Ditolak";
export type Team = {
  id: number;
  name: string;
  category: string;
  detail: string;
  coach: string;
  email: string;
  members: string;
  status: TeamStatus;
};

export const initialTeams: Team[] = [
  {
    id: 1,
    name: "PIXEL · SMP Harapan 2",
    category: "SLTP",
    detail: "Smart Warehouse",
    coach: "Dewi Larasati",
    email: "dewi@harapan.contoh",
    members: "Fajar dan Aisyah",
    status: "Menunggu",
  },
  {
    id: 2,
    name: "LUMEN · SD Pelita",
    category: "SD",
    detail: "Dokumen institusi belum sesuai",
    coach: "Raka Utama",
    email: "raka@pelita.contoh",
    members: "Alya dan Intan",
    status: "Menunggu",
  },
  {
    id: 3,
    name: "ASTRA · SMP Lentera",
    category: "SLTP",
    detail: "Surat pendamping diperbarui",
    coach: "Nina Lestari",
    email: "nina@lentera.contoh",
    members: "Rafi dan Wulan",
    status: "Perlu revisi",
  },
  {
    id: 4,
    name: "COMET · SMA Angkasa",
    category: "SLTA",
    detail: "3 anggota · Data lengkap",
    coach: "Bayu Raka",
    email: "bayu@angkasa.contoh",
    members: "Dio, Sita, dan Beni",
    status: "Menunggu",
  },
  {
    id: 5,
    name: "ORION · SD Nusantara 1",
    category: "SD",
    detail: "Disetujui Nadia Pramesti",
    coach: "Dian Putri",
    email: "dian@nusantara.contoh",
    members: "Nara dan Bimo",
    status: "Terverifikasi",
  },
  {
    id: 6,
    name: "TITAN · SMP Garuda A",
    category: "SLTP",
    detail: "Disetujui Nadia Pramesti",
    coach: "Ari Wibowo",
    email: "ari@garuda.contoh",
    members: "Dita dan Elang",
    status: "Terverifikasi",
  },
  {
    id: 7,
    name: "AURORA · SD Teknologi 5",
    category: "SD",
    detail: "Disetujui 10:52 WIB",
    coach: "Mira Sari",
    email: "mira@aurora.contoh",
    members: "Tia dan Riko",
    status: "Terverifikasi",
  },
  {
    id: 8,
    name: "NOVA · SMK Cendekia",
    category: "SLTA",
    detail: "Identitas tidak sesuai",
    coach: "Tono Adi",
    email: "tono@nova.contoh",
    members: "Niko dan Ayu",
    status: "Ditolak",
  },
];

export type User = { id: number; name: string; role: string; email: string; active: boolean };
export const initialUsers: User[] = [
  {
    id: 1,
    name: "Nadia Pramesti",
    role: "Super Admin",
    email: "super@roborace.contoh",
    active: true,
  },
  {
    id: 2,
    name: "Maya Santoso",
    role: "Event Admin",
    email: "admin@roborace.contoh",
    active: true,
  },
  {
    id: 3,
    name: "Dimas Arya",
    role: "Operator Arena",
    email: "operator@roborace.contoh",
    active: true,
  },
  { id: 4, name: "Sari Puspita", role: "Juri", email: "juri@roborace.contoh", active: true },
  {
    id: 5,
    name: "Bima Pradana",
    role: "Chief Judge",
    email: "chief@roborace.contoh",
    active: true,
  },
  { id: 6, name: "Rani Wulandari", role: "Coach", email: "rani@garuda.contoh", active: true },
  { id: 7, name: "Arga Wijaya", role: "Coach", email: "arga@sekolah.contoh", active: false },
];

export type Audit = {
  category: string;
  action: string;
  actor: string;
  object: string;
  change: string;
  time: string;
};
export const auditRows: Audit[] = [
  {
    category: "Skor",
    action: "Koreksi skor",
    actor: "Bima Pradana",
    object: "VEGA · NR-121",
    change: "Manual 9 → 10",
    time: "11:32",
  },
  {
    category: "Event",
    action: "Publikasi level",
    actor: "Bima Pradana",
    object: "SLTP · Cup 2026",
    change: "Sementara → Resmi",
    time: "11:20",
  },
  {
    category: "Tim",
    action: "Verifikasi tim",
    actor: "Nadia Pramesti",
    object: "AURORA · NR-008",
    change: "Menunggu → Terverifikasi",
    time: "10:52",
  },
  {
    category: "Event",
    action: "Jadwalkan re-run",
    actor: "Dimas Arya",
    object: "ORION · Arena A",
    change: "10:00 → 11:15 WIB",
    time: "10:10",
  },
  {
    category: "Skor",
    action: "Suara pencapaian",
    actor: "Sari Puspita",
    object: "ORION · CP2",
    change: "Belum memilih → Setuju",
    time: "10:02",
  },
  {
    category: "Peran",
    action: "Ubah peran",
    actor: "Nadia Pramesti",
    object: "Maya Santoso",
    change: "Coach → Event Admin",
    time: "09:10",
  },
  {
    category: "Event",
    action: "Buat event",
    actor: "Maya Santoso",
    object: "Internal Sekolah",
    change: "Tidak ada → draft",
    time: "09:02",
  },
  {
    category: "Sistem",
    action: "Backup sistem",
    actor: "Sistem",
    object: "Database RoboRace",
    change: "Dijadwalkan → Berhasil",
    time: "03:00",
  },
  {
    category: "Tim",
    action: "Catatan revisi",
    actor: "Nadia Pramesti",
    object: "ASTRA · NR-055",
    change: "Kosong → Perlu revisi",
    time: "Kemarin",
  },
  {
    category: "Peran",
    action: "Aktifkan akun",
    actor: "Nadia Pramesti",
    object: "Arga Wijaya",
    change: "Nonaktif → Aktif",
    time: "Kemarin",
  },
  {
    category: "Sistem",
    action: "Sinkronisasi",
    actor: "Sistem",
    object: "Leaderboard",
    change: "Tertunda → Selesai",
    time: "Kemarin",
  },
  {
    category: "Event",
    action: "Tutup registrasi",
    actor: "Maya Santoso",
    object: "Regional Seri 1",
    change: "Terbuka → Ditutup",
    time: "12 Nov",
  },
];
