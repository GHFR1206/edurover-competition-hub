export type LevelName = "SD" | "SLTP" | "SLTA";

export type Team = {
  id: string;
  name: string;
  school: string;
  level: LevelName;
  score: number;
  detail: string;
  valid: boolean;
};

export const initialTeams: Team[] = [
  {
    id: "NR-014",
    name: "ORION",
    school: "SD Nusantara 1",
    level: "SD",
    score: 40,
    detail: "2 pencapaian menunggu keputusan Chief",
    valid: false,
  },
  {
    id: "NR-024",
    name: "NOVA",
    school: "SD Inovasi 3",
    level: "SD",
    score: 100,
    detail: "Nilai manual belum lengkap",
    valid: false,
  },
  {
    id: "NR-008",
    name: "AURORA",
    school: "SD Teknologi 5",
    level: "SD",
    score: 110,
    detail: "Seluruh pencapaian dan nilai telah valid",
    valid: true,
  },
  {
    id: "NR-114",
    name: "TITAN",
    school: "SMP Garuda A",
    level: "SLTP",
    score: 112,
    detail: "100 robot · 12 manual · 02:14",
    valid: true,
  },
  {
    id: "NR-121",
    name: "VEGA",
    school: "SMP Inovasi B",
    level: "SLTP",
    score: 108,
    detail: "100 robot · 8 manual · 02:23",
    valid: true,
  },
  {
    id: "NR-211",
    name: "PULSAR",
    school: "SMA Cakrawala",
    level: "SLTA",
    score: 126,
    detail: "110 robot · 16 manual · seluruh data valid",
    valid: true,
  },
  {
    id: "NR-227",
    name: "NEBULA",
    school: "SMA Pionir",
    level: "SLTA",
    score: 121,
    detail: "108 robot · 13 manual · seluruh data valid",
    valid: true,
  },
];

export type ReviewItem = {
  id: string;
  type: "achievement" | "manual";
  eyebrow: string;
  title: string;
  team: string;
  name: string;
  meta: string;
  description?: string;
  badge: string;
  points?: number;
  defaultReason: string;
  evidence?: string;
};

export const reviewItems: ReviewItem[] = [
  {
    id: "cp2",
    type: "achievement",
    eyebrow: "PENCAPAIAN ROBOT · NR-014",
    title: "ORION · CP2 · 20 poin",
    team: "ORION",
    name: "CP2",
    meta: "14 November 2026 · 10:02:36 WIB · Telemetri EduRover",
    description:
      "3 juri setuju · 1 juri tolak. Poin belum disahkan. Periksa apakah robot melewati zona checkpoint sesuai aturan.",
    badge: "Mayoritas 3 dari 4",
    points: 20,
    evidence: "rekaman-cp2.mp4 · 00:18",
    defaultReason:
      "Robot melewati CP2 dengan seluruh roda berada di jalur. Rekaman arena mengonfirmasi pencapaian.",
  },
  {
    id: "nova-manual",
    type: "manual",
    eyebrow: "PENILAIAN JURI · SARI PUSPITA",
    title: "NOVA · Nilai manual 14/20",
    team: "NOVA",
    name: "Nilai manual",
    meta: "SD Inovasi 3 · NR-024 · Bukti: bukti-nova.jpg",
    badge: "Perlu review",
    points: 14,
    defaultReason: "Mohon sertakan sudut kamera yang menunjukkan zona akhir peletakan paket.",
  },
  {
    id: "cp4",
    type: "achievement",
    eyebrow: "PENCAPAIAN ROBOT · NR-014",
    title: "ORION · CP4 · 15 poin",
    team: "ORION",
    name: "CP4",
    meta: "14 November 2026 · 10:04:18 WIB · Telemetri EduRover",
    description:
      "Sensor mencatat checkpoint, tetapi satu rekaman sudut arena perlu dikonfirmasi Chief.",
    badge: "Mayoritas 2 dari 3",
    points: 15,
    evidence: "rekaman-cp4.mp4 · 00:12",
    defaultReason: "",
  },
];

export const correctionTeams = {
  VEGA: { id: "NR-121", school: "SMP Inovasi B", robot: 100, scores: [6, 8, 9], rank: 2 },
  TITAN: { id: "NR-114", school: "SMP Garuda A", robot: 100, scores: [10, 12, 12], rank: 1 },
} as const;
