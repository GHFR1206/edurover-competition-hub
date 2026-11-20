export type Page = "remote" | "manual" | "leaderboard";
export type TeamKey = "ORION" | "NOVA" | "AURORA";

export const teams = {
  ORION: {
    label: "ORION · Berjalan",
    meta: "ORION · NR-014 · Arena A · Panel SD · Juri Sari Puspita",
    quorum: "KUORUM 3 DARI 4 JURI",
    checkpoints: [
      {
        id: "CP1",
        points: 20,
        votes: "4 setuju · 0 tolak",
        progress: 100,
        status: "✓ Disahkan Chief",
        tone: "success",
      },
      {
        id: "CP2",
        points: 20,
        votes: "3 setuju · 1 tolak",
        progress: 55,
        status: "Mayoritas · menunggu Chief",
        tone: "warning",
      },
      {
        id: "CP3",
        points: 20,
        votes: "2 setuju · 1 tolak · 1 belum",
        progress: 36,
        status: "Belum kuorum",
        tone: "neutral",
      },
      {
        id: "FINISH",
        points: 40,
        votes: "0 setuju · 0 tolak · 4 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
    ],
  },
  NOVA: {
    label: "NOVA · Berikutnya",
    meta: "NOVA · NR-024 · Arena A · Panel SD · Juri Sari Puspita",
    quorum: "KUORUM 2 DARI 3 JURI",
    checkpoints: [
      {
        id: "CP1",
        points: 20,
        votes: "3 setuju · 0 tolak",
        progress: 100,
        status: "✓ Disahkan Chief",
        tone: "success",
      },
      {
        id: "CP2",
        points: 20,
        votes: "1 setuju · 1 tolak · 1 belum",
        progress: 34,
        status: "Belum kuorum",
        tone: "neutral",
      },
      {
        id: "CP3",
        points: 20,
        votes: "0 setuju · 0 tolak · 3 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
      {
        id: "FINISH",
        points: 40,
        votes: "0 setuju · 0 tolak · 3 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
    ],
  },
  AURORA: {
    label: "AURORA · Terjadwal",
    meta: "AURORA · NR-031 · Arena B · Panel SD · Juri Sari Puspita",
    quorum: "KUORUM 3 DARI 4 JURI",
    checkpoints: [
      {
        id: "CP1",
        points: 20,
        votes: "0 setuju · 0 tolak · 4 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
      {
        id: "CP2",
        points: 20,
        votes: "0 setuju · 0 tolak · 4 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
      {
        id: "CP3",
        points: 20,
        votes: "0 setuju · 0 tolak · 4 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
      {
        id: "FINISH",
        points: 40,
        votes: "0 setuju · 0 tolak · 4 belum",
        progress: 0,
        status: "Menunggu pencapaian",
        tone: "warning",
      },
    ],
  },
} as const;

export const leaderboard = {
  SD: [
    ["ORION", "NR-014", "SD Pelita Bangsa", "118", "✓ Resmi"],
    ["NOVA", "NR-024", "SD Inovasi 3", "104", "✓ Resmi"],
    ["AURORA", "NR-031", "SD Cakrawala", "—", "◔ Sementara"],
  ],
  SLTP: [
    ["TITAN", "NR-114", "SMP Garuda A", "112", "✓ Resmi"],
    ["VEGA", "NR-121", "SMP Inovasi B", "108", "✓ Resmi"],
    ["PIXEL", "Belum terbit", "SMP Harapan 2 · Menunggu verifikasi", "—", "◔ Sementara"],
  ],
  SLTA: [
    ["GALAXY", "NR-208", "SMA Dirgantara", "126", "✓ Resmi"],
    ["NEBULA", "NR-219", "SMK Teknologi 1", "119", "✓ Resmi"],
    ["COMET", "Belum terbit", "SMA Nusantara · Menunggu verifikasi", "—", "◔ Sementara"],
  ],
} as const;
