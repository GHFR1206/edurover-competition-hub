export type Page = "beranda" | "event" | "jadwal" | "leaderboard" | "hasil" | "arsip";
export type Level = "SD" | "SLTP" | "SLTA";

export const navItems: { id: Page; label: string }[] = [
  { id: "beranda", label: "Beranda" },
  { id: "event", label: "Event" },
  { id: "jadwal", label: "Jadwal" },
  { id: "leaderboard", label: "Leaderboard" },
  { id: "hasil", label: "Hasil Resmi" },
  { id: "arsip", label: "Arsip Hasil" },
];

export const teams: Record<
  Level,
  {
    rank: number;
    name: string;
    school: string;
    code: string;
    score: string;
    status: "Resmi" | "Sementara";
    time: string;
  }[]
> = {
  SD: [
    {
      rank: 1,
      name: "ORION",
      school: "SD Nusantara 1",
      code: "NR-014",
      score: "106",
      status: "Resmi",
      time: "02:31",
    },
    {
      rank: 2,
      name: "AURORA",
      school: "SD Teknologi 5",
      code: "NR-008",
      score: "102",
      status: "Resmi",
      time: "02:44",
    },
    {
      rank: 3,
      name: "KOMET",
      school: "SD Harapan",
      code: "NR-022",
      score: "98",
      status: "Sementara",
      time: "—",
    },
  ],
  SLTP: [
    {
      rank: 1,
      name: "TITAN",
      school: "SMP Garuda A",
      code: "NR-114",
      score: "112",
      status: "Resmi",
      time: "02:14",
    },
    {
      rank: 2,
      name: "VEGA",
      school: "SMP Inovasi B",
      code: "NR-121",
      score: "108",
      status: "Resmi",
      time: "02:28",
    },
    {
      rank: 3,
      name: "PIXEL",
      school: "SMP Harapan 2",
      code: "Belum terbit",
      score: "—",
      status: "Sementara",
      time: "—",
    },
  ],
  SLTA: [
    {
      rank: 1,
      name: "QUASAR",
      school: "SMK Mekatronika",
      code: "NR-203",
      score: "115",
      status: "Resmi",
      time: "02:06",
    },
    {
      rank: 2,
      name: "ZENITH",
      school: "SMA Cendekia 1",
      code: "NR-214",
      score: "109",
      status: "Resmi",
      time: "02:22",
    },
    {
      rank: 3,
      name: "NOVA",
      school: "SMA Angkasa",
      code: "NR-224",
      score: "104",
      status: "Sementara",
      time: "—",
    },
  ],
};

export const schedule = [
  {
    time: "09:30",
    arena: "Arena B",
    team: "TITAN",
    school: "SMP Garuda A",
    level: "SLTP" as Level,
    code: "NR-114",
    status: "Selesai",
  },
  {
    time: "09:45",
    arena: "Arena B",
    team: "VEGA",
    school: "SMP Inovasi B",
    level: "SLTP" as Level,
    code: "NR-121",
    status: "Selesai",
  },
  {
    time: "10:00",
    arena: "Arena A",
    team: "ORION",
    school: "SD Nusantara 1",
    level: "SD" as Level,
    code: "NR-014",
    status: "Sedang berjalan",
  },
  {
    time: "10:15",
    arena: "Arena A",
    team: "NOVA",
    school: "SD Inovasi 3",
    level: "SD" as Level,
    code: "NR-024",
    status: "Berikutnya",
  },
  {
    time: "10:30",
    arena: "Arena C",
    team: "ZENITH",
    school: "SMA Cendekia 1",
    level: "SLTA" as Level,
    code: "NR-214",
    status: "Terjadwal",
  },
  {
    time: "10:45",
    arena: "Arena C",
    team: "QUASAR",
    school: "SMK Mekatronika",
    level: "SLTA" as Level,
    code: "NR-203",
    status: "Terjadwal",
  },
];

export const archives = [
  {
    year: 2026,
    title: "RoboRace Cup 2026 · Seri Pilot",
    place: "GOR Edukasi Nusantara",
    teams: [
      ["SD · ORION", "106"],
      ["SLTP · TITAN", "112"],
      ["SLTA · QUASAR", "115"],
    ],
  },
  {
    year: 2025,
    title: "RoboRace Cup 2025 · Final",
    place: "GOR Edukasi Nusantara · 36 tim dari 18 sekolah",
    teams: [
      ["SD · AURORA", "110"],
      ["SLTP · TITAN", "112"],
      ["SLTA · QUASAR", "115"],
    ],
  },
  {
    year: 2025,
    title: "RoboRace Regional 2025",
    place: "Putaran regional nasional",
    teams: [
      ["SD · ORION", "106"],
      ["SLTP · VEGA", "108"],
    ],
  },
  {
    year: 2024,
    title: "RoboRace Cup 2024 · Final",
    place: "Musim pertama RoboRace",
    teams: [
      ["SD · BIMA", "101"],
      ["SLTP · ATLAS", "104"],
      ["SLTA · VOLT", "109"],
    ],
  },
];
