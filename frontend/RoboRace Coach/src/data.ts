export type SessionStatus = "Selesai" | "Sedang berjalan" | "Berikutnya" | "Terjadwal";

export interface ArenaSession {
  time: string;
  arena: string;
  team: string;
  school: string;
  level: "SD" | "SLTP" | "SLTA";
  robot: string;
  status: SessionStatus;
  day: "today" | "tomorrow";
  isMine?: boolean;
}

export const todaySessions: ArenaSession[] = [
  {
    time: "09:30",
    arena: "Arena B",
    team: "TITAN",
    school: "SMP Garuda A",
    level: "SLTP",
    robot: "NR-114",
    status: "Selesai",
    day: "today",
    isMine: true,
  },
  {
    time: "09:45",
    arena: "Arena B",
    team: "VEGA",
    school: "SMP Inovasi B",
    level: "SLTP",
    robot: "NR-121",
    status: "Selesai",
    day: "today",
  },
  {
    time: "10:00",
    arena: "Arena A",
    team: "ORION",
    school: "SD Nusantara 1",
    level: "SD",
    robot: "NR-014",
    status: "Sedang berjalan",
    day: "today",
  },
  {
    time: "10:15",
    arena: "Arena A",
    team: "NOVA",
    school: "SD Inovasi 3",
    level: "SD",
    robot: "NR-024",
    status: "Berikutnya",
    day: "today",
  },
  {
    time: "10:30",
    arena: "Arena C",
    team: "ZENITH",
    school: "SMA Cendekia 1",
    level: "SLTA",
    robot: "NR-214",
    status: "Terjadwal",
    day: "today",
  },
  {
    time: "10:45",
    arena: "Arena C",
    team: "QUASAR",
    school: "SMK Mekatronika",
    level: "SLTA",
    robot: "NR-203",
    status: "Terjadwal",
    day: "today",
  },
];

export const tomorrowSessions: ArenaSession[] = [
  {
    time: "08:30",
    arena: "Arena A",
    team: "AURORA",
    school: "SD Pelita 2",
    level: "SD",
    robot: "NR-031",
    status: "Terjadwal",
    day: "tomorrow",
  },
  {
    time: "09:00",
    arena: "Arena C",
    team: "GALAXY",
    school: "SMA Tunas Bangsa",
    level: "SLTA",
    robot: "NR-226",
    status: "Terjadwal",
    day: "tomorrow",
  },
];

export const initialMembers = [
  { name: "Nara Putri", detail: "Ketua tim · Kelas VIII A · SMP Garuda A" },
  { name: "Raka Mahendra", detail: "Programmer · Kelas VIII B · SMP Garuda A" },
];

export const achievementSteps = ["START · 0", "CP1 · 20", "CP2 · 20", "CP3 · 20", "FINISH · 40"];
