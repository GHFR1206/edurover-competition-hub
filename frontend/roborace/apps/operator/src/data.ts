export type ArenaId = "A" | "B" | "C";
export type QueueStatus = "Menunggu" | "Dipanggil" | "Siap mulai" | "Berjalan" | "Selesai";

export type QueueItem = {
  id: string;
  date?: string;
  time: string;
  team: string;
  robot: string;
  school: string;
  detail: string;
  status: QueueStatus;
  duration?: string;
};

export type ActiveRun = {
  id: string;
  startTime: string;
  team: string;
  robot: string;
  school: string;
  challenge: string;
  elapsed: number;
};

export type ArenaData = {
  id: ArenaId;
  label: string;
  level: string;
  subtitle: string;
  run: ActiveRun | null;
  queue: QueueItem[];
};

export const initialArenas: Record<ArenaId, ArenaData> = {
  A: {
    id: "A",
    label: "Arena A",
    level: "SD",
    subtitle: "Arena A · SD · Sabtu, 14 November 2026 · Operator Dimas Arya",
    run: {
      id: "run-orion",
      startTime: "10:00",
      team: "ORION",
      robot: "NR-014",
      school: "SD Nusantara 1",
      challenge: "Color Delivery & Adventure",
      elapsed: 156,
    },
    queue: [
      {
        id: "a1",
        time: "10:15",
        team: "NOVA",
        robot: "NR-024",
        school: "SD Inovasi 3",
        detail: "Slot berikutnya · Belum hadir di meja arena",
        status: "Menunggu",
      },
      {
        id: "a2",
        time: "10:30",
        team: "AURORA",
        robot: "NR-008",
        school: "SD Teknologi 5",
        detail: "Sudah dipanggil ke meja arena",
        status: "Dipanggil",
      },
      {
        id: "a3",
        time: "10:45",
        team: "Tim uji arena",
        robot: "",
        school: "Robot siap",
        detail: "Validasi lintasan dan keselamatan selesai",
        status: "Siap mulai",
      },
      {
        id: "a4",
        time: "09:30",
        team: "TITAN",
        robot: "NR-114",
        school: "SMP Garuda A · Arena B",
        detail: "Run selesai 02:14",
        status: "Selesai",
        duration: "02:14",
      },
    ],
  },
  B: {
    id: "B",
    label: "Arena B",
    level: "SLTP",
    subtitle: "Arena B · SLTP · Sabtu, 14 November 2026 · Operator Nabila Putri",
    run: {
      id: "run-polaris",
      startTime: "10:05",
      team: "POLARIS",
      robot: "RB-021",
      school: "SMP Cakrawala 2",
      challenge: "Rescue Track & Precision",
      elapsed: 118,
    },
    queue: [
      {
        id: "b1",
        time: "10:20",
        team: "GALAXY",
        robot: "RB-011",
        school: "SMP Harapan Jaya",
        detail: "Slot berikutnya · Menunggu panggilan",
        status: "Menunggu",
      },
      {
        id: "b2",
        time: "10:35",
        team: "VECTOR",
        robot: "RB-034",
        school: "SMP Teknologi Mandiri",
        detail: "Sudah dipanggil ke meja arena",
        status: "Dipanggil",
      },
      {
        id: "b3",
        time: "10:50",
        team: "NEBULA",
        robot: "RB-009",
        school: "SMP Bina Bangsa",
        detail: "Robot dan baterai telah diperiksa",
        status: "Siap mulai",
      },
      {
        id: "b4",
        time: "09:40",
        team: "COMET",
        robot: "RB-017",
        school: "SMP Pelita Nusantara",
        detail: "Run selesai 03:08",
        status: "Selesai",
        duration: "03:08",
      },
    ],
  },
  C: {
    id: "C",
    label: "Arena C",
    level: "SLTA",
    subtitle: "Arena C · SLTA · Sabtu, 14 November 2026 · Operator Raka Mahendra",
    run: {
      id: "run-zenith",
      startTime: "10:10",
      team: "ZENITH",
      robot: "RC-072",
      school: "SMA Sains Utama",
      challenge: "Autonomous City Mission",
      elapsed: 204,
    },
    queue: [
      {
        id: "c1",
        time: "10:25",
        team: "ECLIPSE",
        robot: "RC-044",
        school: "SMA Negeri 8",
        detail: "Slot berikutnya · Belum hadir di meja arena",
        status: "Menunggu",
      },
      {
        id: "c2",
        time: "10:40",
        team: "QUANTUM",
        robot: "RC-019",
        school: "SMK Robotika Nusantara",
        detail: "Sudah dipanggil ke meja arena",
        status: "Dipanggil",
      },
      {
        id: "c3",
        time: "10:55",
        team: "PIONIR",
        robot: "RC-063",
        school: "SMA Karya Bangsa",
        detail: "Robot siap · Pemeriksaan keselamatan selesai",
        status: "Siap mulai",
      },
      {
        id: "c4",
        time: "09:55",
        team: "VORTEX",
        robot: "RC-031",
        school: "SMK Teknologi 1",
        detail: "Run selesai 04:02",
        status: "Selesai",
        duration: "04:02",
      },
    ],
  },
};

export const arenaIds: ArenaId[] = ["A", "B", "C"];

export function formatDuration(seconds: number) {
  const safe = Math.min(seconds, 300);
  return `${String(Math.floor(safe / 60)).padStart(2, "0")}:${String(safe % 60).padStart(2, "0")}`;
}
