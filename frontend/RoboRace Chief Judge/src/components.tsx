import type { ReactNode } from "react";

export type Page = "review" | "judging" | "correction" | "results" | "leaderboard";

const nav: { id: Page; label: string }[] = [
  { id: "review", label: "Review & Publikasi" },
  { id: "judging", label: "Tinjau Penilaian" },
  { id: "correction", label: "Koreksi Resmi" },
  { id: "results", label: "Hasil Resmi" },
  { id: "leaderboard", label: "Leaderboard" },
];

export function Navbar({ page, navigate }: { page: Page; navigate: (page: Page) => void }) {
  return (
    <nav className="navbar">
      <button className="brand" onClick={() => navigate("review")} aria-label="RoboRace">
        Robo<span>Race</span>
      </button>
      <div className="navlinks">
        {nav.map((item) => (
          <button
            key={item.id}
            className={page === item.id ? "active" : ""}
            onClick={() => navigate(item.id)}
          >
            {item.label}
          </button>
        ))}
      </div>
      <span className="role-chip">Chief Judge</span>
    </nav>
  );
}

const pageIntro = {
  review: [
    "Validasi tuntas. Juara jelas.",
    "Periksa kelengkapan per level sebelum mengesahkan hasil publik.",
    "/assets/786e4.png",
  ],
  judging: [
    "Bukti dulu, keputusan kemudian.",
    "Tinjau pencapaian robot dan penilaian manual yang membutuhkan keputusan.",
    "/assets/6db8f.png",
  ],
  correction: [
    "Koreksi transparan, hasil tepercaya.",
    "Perubahan setelah publikasi wajib memiliki alasan dan jejak audit.",
    "/assets/6db8f.png",
  ],
  results: ["Hasil Resmi", "Smart Warehouse Challenge · Batas waktu 6 menit", "/assets/786e4.png"],
  leaderboard: [
    "Peringkat cepat, hasil akurat.",
    "Pantau peringkat setiap jenjang dari hasil yang telah ditinjau Chief.",
    "/assets/786e4.png",
  ],
};

export function PageShell({
  page,
  navigate,
  children,
}: {
  page: Page;
  navigate: (page: Page) => void;
  children: ReactNode;
}) {
  const [title, subtitle, mascot] = pageIntro[page];
  if (page === "results") {
    return (
      <main className="results-main">
        <Navbar page={page} navigate={navigate} />
        {children}
      </main>
    );
  }
  return (
    <main>
      <Navbar page={page} navigate={navigate} />
      <header className="hero">
        <div className="event-row">
          <span className="live">● LANGSUNG · 14 NOV 2026</span>
          <span>Seri Pilot</span>
        </div>
        <div className="hero-row">
          <div>
            <h1>{title}</h1>
            <p>{subtitle}</p>
          </div>
          <img className="hero-mascot" src={mascot} alt="Maskot RoboRace" />
        </div>
      </header>
      <section className="content">
        <img className="track-decoration" src="/assets/98d5a.svg" alt="" />
        <div className="gear gear-one">✿</div>
        <div className="hexagon" />
        <div className="triangle" />
        <div className="flag" />
        {children}
      </section>
      <Footer />
    </main>
  );
}

export function Card({ children, className = "" }: { children: ReactNode; className?: string }) {
  return <div className={`card ${className}`}>{children}</div>;
}

export function Badge({
  children,
  tone = "purple",
}: {
  children: ReactNode;
  tone?: "purple" | "yellow" | "green" | "red";
}) {
  return <span className={`badge ${tone}`}>{children}</span>;
}

export function Button({
  children,
  kind = "outline",
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement> & {
  kind?: "primary" | "yellow" | "outline" | "danger";
}) {
  return (
    <button className={`button ${kind}`} {...props}>
      {children}
    </button>
  );
}

export function Footer({ waveAsset = "/assets/c9747.svg" }: { waveAsset?: string }) {
  return (
    <footer>
      <div className="footer-wave">
        <img src={waveAsset} alt="" />
      </div>
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
