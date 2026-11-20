import type { ReactNode } from "react";
import { navItems, type Level, type Page } from "./data";

export const A = "/assets";

export function Header({
  page,
  go,
  dark = false,
}: {
  page: Page;
  go: (page: Page) => void;
  dark?: boolean;
}) {
  return (
    <header className={`site-header ${dark ? "dark-header" : ""}`}>
      <button className="logo" onClick={() => go("beranda")}>
        <span>Robo</span>
        <b>Race</b>
      </button>
      <nav aria-label="Navigasi utama">
        {navItems.map((item) => (
          <button
            key={item.id}
            className={page === item.id ? "active" : ""}
            onClick={() => go(item.id)}
          >
            {item.label}
          </button>
        ))}
      </nav>
      <div className="account-actions">
        <button className="outline-btn">Masuk</button>
        <button className="gold-btn">Daftar sekolah</button>
      </div>
    </header>
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

export function LiveBadge() {
  return (
    <div className="badge-row">
      <span className="live">● LANGSUNG · 14 NOV 2026</span>
      <span className="series">Seri Pilot</span>
    </div>
  );
}

export function Mascot({ src, className = "" }: { src: string; className?: string }) {
  return <img className={`mascot float ${className}`} src={`${A}/${src}`} alt="Maskot RoboRace" />;
}

export function PageHero({
  title,
  description,
  mascot,
  children,
  center = false,
}: {
  title: string;
  description: string;
  mascot: string;
  children?: ReactNode;
  center?: boolean;
}) {
  return (
    <section className={`page-hero ${center ? "center" : ""}`}>
      <div>
        <LiveBadge />
        {children}
        <h1>{title}</h1>
        <p>{description}</p>
      </div>
      <Mascot src={mascot} />
    </section>
  );
}

export function LevelChips({
  active,
  setActive,
  dark = false,
}: {
  active: Level;
  setActive: (level: Level) => void;
  dark?: boolean;
}) {
  return (
    <div className={`chips ${dark ? "dark-chips" : ""}`} role="group" aria-label="Filter jenjang">
      {(["SD", "SLTP", "SLTA"] as Level[]).map((level) => (
        <button
          key={level}
          className={active === level ? "active" : ""}
          onClick={() => setActive(level)}
        >
          {level}
        </button>
      ))}
    </div>
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
    <article className={`card reveal ${className}`} style={{ animationDelay: `${delay}ms` }}>
      {children}
    </article>
  );
}

export function Toast({ children }: { children: ReactNode }) {
  return (
    <div className="toast" role="status">
      {children}
    </div>
  );
}
