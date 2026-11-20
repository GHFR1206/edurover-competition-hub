export type PageName = "login" | "register" | "forgot" | "check-email";

export type IntroContent = {
  eyebrow?: string;
  title: string;
  description: string;
  mascot: string;
  mascotAlt: string;
  note?: string;
  noteMascot?: string;
};

export const navItems = ["Beranda", "Event", "Jadwal", "Leaderboard", "Hasil Resmi", "Arsip Hasil"];

export const pageContent: Record<PageName, IntroContent> = {
  login: {
    eyebrow: "SELAMAT DATANG KEMBALI",
    title: "Siap melaju,\nsiap jadi juara?",
    description: "Saatnya berkompetisi, berkolaborasi, dan mencetak prestasi!",
    mascot: "/assets/17d7a.png",
    mascotAlt: "Maskot RoboRace membawa laptop",
    note: "Siap berlomba? Mari berikan yang terbaik!",
  },
  register: {
    eyebrow: "SATU SEKOLAH, BANYAK KEMUNGKINAN",
    title: "Ide hebat dimulai dari timmu!",
    description: "Buat akun, daftarkan sekolah, lalu ajak robotmu menjelajah lintasan.",
    mascot: "/assets/dc500.png",
    mascotAlt: "Maskot RoboRace menyambut tim baru",
    note: "Data sekolahmu membantu panitia memverifikasi tim. Isi dengan teliti, ya!",
    noteMascot: "/assets/e638f.png",
  },
  forgot: {
    title: "Tenang, kita cari jalannya",
    description: "Masukkan emailmu dan kami kirim tautan untuk membuat kata sandi baru.",
    mascot: "/assets/6db8f.png",
    mascotAlt: "Maskot RoboRace sedang berpikir",
  },
  "check-email": {
    title: "Tenang, kita cari jalannya",
    description: "Masukkan emailmu dan kami kirim tautan untuk membuat kata sandi baru.",
    mascot: "/assets/6db8f.png",
    mascotAlt: "Maskot RoboRace sedang berpikir",
  },
};

export const passwordRules = [
  { label: "Minimal 8 karakter", test: (value: string) => value.length >= 8 },
  { label: "Huruf kecil (a-z)", test: (value: string) => /[a-z]/.test(value) },
  { label: "Huruf besar (A-Z)", test: (value: string) => /[A-Z]/.test(value) },
  { label: "Angka (0-9)", test: (value: string) => /\d/.test(value) },
];
