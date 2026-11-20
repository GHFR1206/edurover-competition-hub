import type { Page, SessionStatus } from "./data";

const menu: { id: Page; label: string }[] = [
  { id: "events", label: "Event & Ruleset" },
  { id: "staff", label: "Staf & Penugasan" },
  { id: "schedule", label: "Jadwal & Arena" },
  { id: "reports", label: "Laporan" },
];

export function Navbar({ page, onNavigate }: { page: Page; onNavigate: (page: Page) => void }) {
  const active = page === "setup" ? "events" : page;
  return (
    <header className="navbar">
      <button className="brand" onClick={() => onNavigate("events")} aria-label="RoboRace beranda">
        Robo<span>Race</span>
      </button>
      <nav aria-label="Navigasi admin">
        {menu.map((item) => (
          <button
            key={item.id}
            className={active === item.id ? "active" : ""}
            onClick={() => onNavigate(item.id)}
          >
            {item.label}
          </button>
        ))}
      </nav>
      <span className="admin-label">Event Admin</span>
    </header>
  );
}

const heroCopy: Record<Page, [string, string]> = {
  events: [
    "Rancang arena yang hebat.",
    "Kelola event dan tahap kompetisi, dari ide sampai garis finis.",
  ],
  setup: [
    "Siapkan tantangannya!",
    "Kejuaraan Internal Sekolah · Setup sebelum pendaftaran dibuka.",
  ],
  staff: [
    "Tim di balik lintasan",
    "Atur juri, operator, dan Chief Judge untuk kompetisi yang adil.",
  ],
  schedule: [
    "Semua punya giliran melesat.",
    "Susun sesi tanpa bentrok dan jaga ritme setiap arena.",
  ],
  reports: [
    "Cerita lomba dalam angka.",
    "RoboRace Cup 2026 (Seri Pilot) · Ringkasan panitia dan unduhan.",
  ],
};

export function Hero({ page }: { page: Page }) {
  const [title, description] = heroCopy[page];
  return (
    <section className="hero">
      <div className="live-row">
        <span className="live">● LANGSUNG · 14 NOV 2026</span>
        <span>Seri Pilot</span>
      </div>
      <div className="hero-content">
        <div>
          <h1>{title}</h1>
          <p>{description}</p>
        </div>
        <img
          className="mascot"
          src={page === "staff" ? "/assets/dc500.png" : "/assets/17d7a.png"}
          alt="Maskot RoboRace"
        />
      </div>
    </section>
  );
}

export function PageShell({
  page,
  onNavigate,
  children,
}: {
  page: Page;
  onNavigate: (page: Page) => void;
  children: React.ReactNode;
}) {
  return (
    <div className={`app page-${page}`}>
      <Navbar page={page} onNavigate={onNavigate} />
      <Hero page={page} />
      <main>
        <Decorations />
        {children}
      </main>
      <Footer />
    </div>
  );
}

function Decorations() {
  return (
    <div className="decorations" aria-hidden="true">
      <span className="gear">✤</span>
      <span className="flag">▦</span>
      <span className="hex">⬢</span>
      <span className="triangle">▲</span>
    </div>
  );
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

export function Card({
  children,
  className = "",
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return <section className={`card ${className}`}>{children}</section>;
}

export function Button({
  children,
  variant = "outline",
  disabled,
  onClick,
  type = "button",
}: {
  children: React.ReactNode;
  variant?: "outline" | "primary" | "yellow";
  disabled?: boolean;
  onClick?: () => void;
  type?: "button" | "submit";
}) {
  return (
    <button className={`button ${variant}`} disabled={disabled} onClick={onClick} type={type}>
      {children}
    </button>
  );
}

export function Chip({
  children,
  active,
  onClick,
}: {
  children: React.ReactNode;
  active?: boolean;
  onClick?: () => void;
}) {
  return (
    <button type="button" className={`chip ${active ? "active" : ""}`} onClick={onClick}>
      {children}
    </button>
  );
}

export function Field({
  label,
  value,
  onChange,
  type = "text",
  placeholder,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  type?: string;
  placeholder?: string;
}) {
  return (
    <label className="field">
      <span>{label}</span>
      <input
        type={type}
        value={value}
        placeholder={placeholder}
        onChange={(event) => onChange(event.target.value)}
      />
    </label>
  );
}

export function Notice({
  children,
  tone = "success",
}: {
  children: React.ReactNode;
  tone?: "success" | "warning" | "error";
}) {
  return (
    <div className={`notice ${tone}`} role="status">
      {children}
    </div>
  );
}

export function StatusBadge({ status }: { status: string | SessionStatus }) {
  const key = status.toLowerCase().replace(/\s+/g, "-");
  return <span className={`badge badge-${key}`}>{status}</span>;
}
