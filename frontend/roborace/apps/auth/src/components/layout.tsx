import type { ReactNode } from "react";
import { navItems, type IntroContent, type PageName } from "../data/authData";

type NavigateProps = { onNavigate: (page: PageName) => void };

export function Header({ onNavigate }: NavigateProps) {
  return (
    <header className="header">
      <button
        className="brand"
        type="button"
        onClick={() => onNavigate("login")}
        aria-label="RoboRace"
      >
        Robo<span>Race</span>
      </button>
      <nav className="nav" aria-label="Navigasi utama">
        {navItems.map((item) => (
          <a href={`#${item.toLowerCase().replace(" ", "-")}`} key={item}>
            {item}
          </a>
        ))}
      </nav>
      <div className="header-actions">
        <button className="header-login" type="button" onClick={() => onNavigate("login")}>
          Masuk
        </button>
        <button className="header-register" type="button" onClick={() => onNavigate("register")}>
          Daftar sekolah
        </button>
      </div>
    </header>
  );
}

type AuthLayoutProps = {
  content: IntroContent;
  children: ReactNode;
  register?: boolean;
  compact?: boolean;
};

export function AuthLayout({
  content,
  children,
  register = false,
  compact = false,
}: AuthLayoutProps) {
  return (
    <div
      className={`auth-layout ${register ? "auth-layout--register" : ""} ${compact ? "auth-layout--compact" : ""}`}
    >
      <section className="intro">
        {content.eyebrow && <span className="intro-eyebrow">{content.eyebrow}</span>}
        <h1>{content.title}</h1>
        <p>{content.description}</p>
        <img className="intro-mascot" src={content.mascot} alt={content.mascotAlt} />
        {register && content.note && (
          <div className="mascot-note mascot-note--register">
            <img src={content.noteMascot} alt="" />
            <span>{content.note}</span>
          </div>
        )}
      </section>
      <section className="auth-column">
        {children}
        {!register && !compact && content.note && (
          <div className="mascot-note">
            <img src={content.mascot} alt="" />
            <span>{content.note}</span>
          </div>
        )}
      </section>
    </div>
  );
}

export function Footer({ register = false }: { register?: boolean }) {
  return (
    <footer className={`footer ${register ? "footer--register" : ""}`}>
      <div className="finish-wave" />
      <div className="footer-content">
        <strong>RoboRace</strong>
        <span>Ditenagai Kreativitas. Digerakkan Teknologi.</span>
        <div />
        <small>© 2026 RoboRace. All Rights Reserved.</small>
      </div>
      <div className="checkerboard" />
    </footer>
  );
}
