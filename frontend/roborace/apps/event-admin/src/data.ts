export type Page = "events" | "setup" | "staff" | "schedule" | "reports";
export type EventStatus = "Draft" | "Open Registrasi" | "Ongoing" | "Selesai";

export type EventItem = {
  id: number;
  name: string;
  detail: string;
  status: EventStatus;
  setupComplete: boolean;
};

export const initialEvents: EventItem[] = [
  {
    id: 1,
    name: "Kejuaraan Internal Sekolah",
    detail: "22 Januari 2027 · Aula Sekolah Nusantara · Setup belum lengkap",
    status: "Draft",
    setupComplete: false,
  },
  {
    id: 2,
    name: "RoboRace Regional Seri 2",
    detail: "Pendaftaran hingga 30 Desember 2026 · 12 tim terdaftar",
    status: "Open Registrasi",
    setupComplete: true,
  },
  {
    id: 3,
    name: "RoboRace Cup 2026 (Seri Pilot)",
    detail: "14–15 November 2026 · GOR Edukasi Nusantara · 48 tim",
    status: "Ongoing",
    setupComplete: true,
  },
  {
    id: 4,
    name: "RoboRace Cup 2025 · Final",
    detail: "15–16 November 2025 · Hasil resmi sudah diarsipkan",
    status: "Selesai",
    setupComplete: true,
  },
];

export type StaffItem = {
  id: number;
  name: string;
  role: string;
  detail: string;
};

export const initialStaff: StaffItem[] = [
  { id: 1, name: "Dimas Arya", role: "Operator Arena", detail: "Arena A · SD · Sesi 08:00–12:00" },
  { id: 2, name: "Sari Puspita", role: "Juri", detail: "Level SLTP · Arena B · Panel 4 juri" },
  {
    id: 3,
    name: "Bima Pradana",
    role: "Chief Judge",
    detail: "Semua level · Review akhir dan publikasi hasil",
  },
];

export type SessionStatus = "Selesai" | "Berjalan" | "Terjadwal";
export type SessionItem = {
  id: number;
  time: string;
  robot: string;
  team: string;
  arena: string;
  level: string;
  duration: number;
  date: string;
  status: SessionStatus;
};

export const initialSessions: SessionItem[] = [
  {
    id: 1,
    time: "09:30",
    robot: "TITAN · NR-114",
    team: "SMP Garuda A",
    arena: "Arena B",
    level: "SLTP",
    duration: 15,
    date: "2026-11-14",
    status: "Selesai",
  },
  {
    id: 2,
    time: "10:00",
    robot: "ORION · NR-014",
    team: "SD Nusantara 1",
    arena: "Arena A",
    level: "SD",
    duration: 15,
    date: "2026-11-14",
    status: "Berjalan",
  },
  {
    id: 3,
    time: "10:15",
    robot: "NOVA · NR-024",
    team: "SD Inovasi 3",
    arena: "Arena A",
    level: "SD",
    duration: 15,
    date: "2026-11-14",
    status: "Terjadwal",
  },
  {
    id: 4,
    time: "10:30",
    robot: "ZENITH · NR-214",
    team: "SMA Cendekia 1",
    arena: "Arena C",
    level: "SLTA",
    duration: 15,
    date: "2026-11-14",
    status: "Terjadwal",
  },
];

export const rulesets = [
  {
    level: "SD",
    weights: [
      ["Misi", "60%"],
      ["Akurasi", "25%"],
      ["Waktu", "15%"],
    ],
  },
  {
    level: "SLTP",
    weights: [
      ["Misi", "45%"],
      ["Checkpoint", "25%"],
      ["Keselamatan", "15%"],
      ["Efisiensi", "15%"],
    ],
  },
  {
    level: "SLTA",
    weights: [
      ["Misi", "45%"],
      ["Navigasi", "20%"],
      ["Otonomi", "20%"],
      ["Efisiensi", "15%"],
    ],
  },
];

export const reportRows = [
  ["Daftar peserta lengkap", "48 tim · nama anggota, jenjang, status verifikasi · CSV"],
  ["Rekap sekolah", "24 sekolah · coach pendamping dan kontak · CSV"],
  ["Rekap kehadiran arena", "36 selesai · 1 DNF · 1 no-show · CSV"],
];
