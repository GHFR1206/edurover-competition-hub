import type { ReactNode } from "react";
import type { Page } from "../App";

const menu: { id: Page; label: string }[] = [
  { id: "team", label: "Tim saya" },
  { id: "results", label: "Hasil" },
  { id: "schedule", label: "Jadwal" },
];

export function AppShell({
  page,
  onNavigate,
  children,
}: {
  page: Page;
  onNavigate: (page: Page) => void;
  children: ReactNode;
}) {
  return (
    <div className="app-shell">
      <header className="navbar">
        <button
          className="brand"
          onClick={() => onNavigate("team")}
          aria-label="RoboRace, buka Tim saya"
        >
          <span>Robo</span>
          <span>Race</span>
        </button>
        <nav aria-label="Menu coach">
          {menu.map((item) => (
            <button
              key={item.id}
              className={`nav-item ${page === item.id ? "active" : ""}`}
              onClick={() => onNavigate(item.id)}
            >
              {item.label}
            </button>
          ))}
        </nav>
        <span className="coach-chip">Coach</span>
      </header>
      {children}
      <Footer />
    </div>
  );
}

function Footer() {
  return (
    <>
      <div className="footer-wave" aria-hidden="true">
        <img src="/assets/shared/c9747.svg" alt="" />
      </div>
      <footer>
        <strong>RoboRace</strong>
        <span>Powered by Creativity. Driven by Technology.</span>
        <div />
        <small>© 2026 RoboRace. All Rights Reserved.</small>
      </footer>
      <div className="checkerboard" aria-hidden="true" />
    </>
  );
}

export function PageHero({
  title,
  subtitle,
  mascot,
  schedule = false,
}: {
  title: string;
  subtitle: string;
  mascot: string;
  schedule?: boolean;
}) {
  return (
    <section className={`hero ${schedule ? "hero-schedule" : ""}`}>
      <div className="content-width">
        <div className="event-row">
          <span className="live-chip">● LANGSUNG · 14 NOV 2026</span>
          <span className="series-chip">Seri Pilot</span>
        </div>
        <div className="hero-row">
          <div>
            <h1>{title}</h1>
            <p>{subtitle}</p>
          </div>
          <img className={`hero-mascot ${schedule ? "schedule-mascot" : ""}`} src={mascot} alt="" />
        </div>
      </div>
    </section>
  );
}

export function ContentZone({ ornament, children }: { ornament: string; children: ReactNode }) {
  return (
    <main className="content-zone">
      <div className="ornament" aria-hidden="true">
        <img src={ornament} alt="" />
        <span className="gear-cut">
          <img src={ornament} alt="" />
        </span>
      </div>
      <div className="content-width page-stack">{children}</div>
    </main>
  );
}

export function Card({
  children,
  className = "",
  delay = 0,
}: {
  children: ReactNode;
  className?: string;
  delay?: number;
}) {
  return (
    <section className={`card reveal ${className}`} style={{ animationDelay: `${delay}ms` }}>
      {children}
    </section>
  );
}

export function StatusBadge({
  children,
  tone = "success",
}: {
  children: ReactNode;
  tone?: "success" | "warning" | "purple";
}) {
  return <span className={`status-badge ${tone}`}>{children}</span>;
}

export function Toast({ message }: { message: string }) {
  return message ? (
    <div className="toast" role="status">
      {message}
    </div>
  ) : null;
}
