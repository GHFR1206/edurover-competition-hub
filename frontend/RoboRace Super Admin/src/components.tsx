import type { HTMLAttributes, ReactNode } from "react";
import { navItems, type Page } from "./data";

export function Navbar({ page, onNavigate }: { page: Page; onNavigate: (page: Page) => void }) {
  return (
    <nav className="navbar">
      <button className="brand" onClick={() => onNavigate("ringkasan")}>
        <span>Robo</span>
        <b>Race</b>
      </button>
      <div className="navlinks">
        {navItems.map((item) => (
          <button
            key={item.id}
            className={page === item.id ? "active" : ""}
            onClick={() => onNavigate(item.id)}
          >
            {item.label}
          </button>
        ))}
      </div>
      <span className="admin-label">Super Admin</span>
    </nav>
  );
}

const mascotByPage: Partial<Record<Page, string>> = {
  verifikasi: "/assets/d8b40.png",
  pengguna: "/assets/dc500.png",
};

const pageCopy: Record<Page, [string, string]> = {
  ringkasan: [
    "Halo, Super Admin!",
    "Semua arena dalam pantauan. Mari jaga kompetisi tetap seru, aman, dan adil.",
  ],
  verifikasi: [
    "Bantu tim menuju arena.",
    "Periksa data sekolah, anggota, dan catatan sebelum memberi akses robot.",
  ],
  pengguna: [
    "Orang hebat di balik robot.",
    "Kelola akses dengan peran yang tepat untuk setiap anggota komunitas.",
  ],
  audit: [
    "Setiap aksi punya jejak.",
    "Pelaku, objek, dan perubahan tersimpan untuk menjaga transparansi kompetisi.",
  ],
  pengaturan: [
    "Aturan sistem, tetap terarah.",
    "Atur keamanan akun dan metode penilaian default RoboRace.",
  ],
};

export function PageHero({ page, children }: { page: Page; children?: ReactNode }) {
  const [title, subtitle] = pageCopy[page];
  return (
    <header className={`page-hero ${page === "ringkasan" ? "summary-hero" : ""}`}>
      <div className="live-row">
        <span className="live-pill">
          {page === "ringkasan" ? "✦ PUSAT KENDALI ROBO RACE" : "● LANGSUNG · 14 NOV 2026"}
        </span>
        {page !== "ringkasan" && <span className="series">Seri Pilot</span>}
      </div>
      <div className="hero-copy">
        <div>
          <h1>{title}</h1>
          <p>{subtitle}</p>
          {children}
        </div>
        <img className="mascot" src={mascotByPage[page] || "/assets/17d7a.png"} alt="" />
      </div>
    </header>
  );
}

export function Card({ children, className = "", ...props }: HTMLAttributes<HTMLElement>) {
  return (
    <section className={`card ${className}`} {...props}>
      {children}
    </section>
  );
}

export function Button({
  children,
  variant = "outline",
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: "primary" | "yellow" | "outline" | "danger";
}) {
  return (
    <button className={`button ${variant}`} {...props}>
      {children}
    </button>
  );
}

export function Status({ children, tone = "purple" }: { children: ReactNode; tone?: string }) {
  return <span className={`status ${tone}`}>{children}</span>;
}

export function Footer() {
  return (
    <footer>
      <strong>RoboRace</strong>
      <span>Powered by Creativity. Driven by Technology.</span>
      <i />
      <small>© 2026 RoboRace. All Rights Reserved.</small>
      <div className="checker" />
    </footer>
  );
}

export function Toast({ message }: { message: string }) {
  return message ? (
    <div className="toast" role="status">
      {message}
    </div>
  ) : null;
}
