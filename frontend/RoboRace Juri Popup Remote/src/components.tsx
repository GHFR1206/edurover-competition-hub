import { useEffect, useState } from "react";
import type { Page } from "./data";

const A = "/assets/";

export function Chip({
  children,
  active = false,
  tone = "",
  onClick,
}: {
  children: React.ReactNode;
  active?: boolean;
  tone?: string;
  onClick?: () => void;
}) {
  return (
    <button className={`chip ${active ? "active" : ""} ${tone}`} onClick={onClick} type="button">
      {children}
    </button>
  );
}

export function Navbar({
  page,
  setPage,
  online,
  toggleOnline,
  remoteOpen,
  setRemoteOpen,
  pending,
  notify,
}: {
  page: Page;
  setPage: (page: Page) => void;
  online: boolean;
  toggleOnline: () => void;
  remoteOpen: boolean;
  setRemoteOpen: (value: boolean) => void;
  pending: number;
  notify: (message: string) => void;
}) {
  const [accountOpen, setAccountOpen] = useState(false);
  return (
    <>
      <nav className="navbar">
        <button className="brand" onClick={() => setPage("remote")} type="button">
          Robo<span>Race</span>
        </button>
        <div className="navlinks">
          <button className={page === "remote" ? "active" : ""} onClick={() => setPage("remote")}>
            Suara Remote
          </button>
          <button className={page === "manual" ? "active" : ""} onClick={() => setPage("manual")}>
            Nilai Manual
          </button>
          <button
            className={page === "leaderboard" ? "active" : ""}
            onClick={() => setPage("leaderboard")}
          >
            Leaderboard
          </button>
        </div>
        <div className="navtools">
          <button className={`connection ${online ? "" : "offline"}`} onClick={toggleOnline}>
            {online ? "● Online" : "○ Offline"}
          </button>
          <button className="remote-nav" onClick={() => setRemoteOpen(!remoteOpen)}>
            <img src={`${A}cd366.svg`} alt="" />
            {remoteOpen ? "Tutup remote" : "Remote"}
            <b>{pending}</b>
          </button>
          <div className="account-wrap">
            <button className="account" onClick={() => setAccountOpen(!accountOpen)}>
              <i>SP</i>
              <span>Juri 2 · Arena A</span>
              <span>⌄</span>
            </button>
            {accountOpen && (
              <div className="account-menu">
                <button
                  onClick={() => {
                    setAccountOpen(false);
                    notify("Kamu tetap masuk pada sesi juri ini.");
                  }}
                >
                  Keluar
                </button>
              </div>
            )}
          </div>
        </div>
      </nav>
      {!online && (
        <div className="offline-banner">
          Kamu offline. Pilihan tersimpan di perangkat dan akan dikirim otomatis.
        </div>
      )}
    </>
  );
}

export function PageHero({ kind = "remote" }: { kind?: "remote" | "manual" }) {
  const manual = kind === "manual";
  return (
    <header className="page-hero">
      <div>
        <div className="event-row">
          <span>● LANGSUNG · 14 NOV 2026</span>
          <b>Seri Pilot</b>
        </div>
        <h1>{manual ? "Amati detail. Nilai dengan adil." : "Satu suara, untuk lomba adil."}</h1>
        <p>
          {manual
            ? "Juri Sari Puspita · Tim NOVA · SD Inovasi 3 · Parameter manual sesuai level"
            : "ORION · NR-014 · Arena A · Panel SD · Juri Sari Puspita"}
        </p>
      </div>
      <img
        className="hero-mascot"
        src={`${A}${manual ? "17d7a.png" : "d8b40.png"}`}
        alt="Maskot RoboRace"
      />
    </header>
  );
}

export function Footer({ dark = false }: { dark?: boolean }) {
  return (
    <footer className={dark ? "dark" : ""}>
      <strong>RoboRace</strong>
      <span>Powered by Creativity. Driven by Technology.</span>
      <hr />
      <small>© 2026 RoboRace. All Rights Reserved.</small>
      <div className="checker" />
    </footer>
  );
}

export type Vote = "setuju" | "tolak";

export function RemotePopup({
  open,
  onClose,
  online,
  pending,
  current,
  lastVote,
  review,
  synced,
  onVote,
  onUndo,
  onReview,
}: {
  open: boolean;
  onClose: () => void;
  online: boolean;
  pending: number;
  current: 2 | 3;
  lastVote: Vote | null;
  review: boolean;
  synced: boolean;
  onVote: (vote: Vote) => void;
  onUndo: () => void;
  onReview: () => void;
}) {
  const [flip, setFlip] = useState(false);
  useEffect(() => {
    setFlip(true);
    const id = window.setTimeout(() => setFlip(false), 520);
    return () => window.clearTimeout(id);
  }, [current, pending, review]);
  if (!open) return null;
  const empty = pending === 0;
  const code = current === 2 ? "CP2" : "CP3";
  const time = current === 2 ? "10:02:41" : "10:03:12";
  return (
    <div
      className="popup-layer"
      onMouseDown={(e) => e.target === e.currentTarget && window.innerWidth > 600 && onClose()}
    >
      {!online && (
        <div className="mobile-offline">
          Kamu offline. Pilihan tersimpan di perangkat dan akan dikirim otomatis.
        </div>
      )}
      <section className="remote-popup" role="dialog" aria-modal="true" aria-label="Remote Juri">
        <div className="popup-head">
          <div className="popup-title">
            <h2>Remote Juri</h2>
            <button onClick={onClose} aria-label="Tutup popup">
              ×
            </button>
          </div>
          <div className="popup-identity">
            <span>Juri 2 · Arena A · SD Explorer</span>
            <b className={online ? "" : "offline"}>{online ? "● Online" : "○ Offline"}</b>
          </div>
        </div>
        <div className={`lcd ${flip ? "flip" : ""}`}>
          <div className="lcd-top">
            <span>Device</span>
            <small>Waktu server</small>
          </div>
          {empty ? (
            <div className="empty-lcd">
              <img src={`${A}d8b40.png`} alt="" />
              <strong>Belum ada pencapaian baru.</strong>
            </div>
          ) : review ? (
            <div className="lcd-center review">
              <strong>⚖ Ditinjau ulang</strong>
              <b>Seimbang, poin ditahan</b>
            </div>
          ) : (
            <div className="lcd-center">
              <strong>{code}</strong>
              <b>+20 poin</b>
            </div>
          )}
          {!empty && (
            <div className="lcd-meta">
              <span>NOVA · NR-024</span>
              <time>{time}</time>
            </div>
          )}
          <small className="lcd-note">Data pendukung, belum resmi.</small>
        </div>
        <div className="vote-actions">
          <button disabled={empty} className="reject" onClick={() => onVote("tolak")}>
            <img src={`${A}495af.svg`} alt="" />
            <span>Tolak</span>
          </button>
          <button disabled={empty} className="approve" onClick={() => onVote("setuju")}>
            <img src={`${A}2e587.svg`} alt="" />
            <span>Setujui</span>
          </button>
        </div>
        {lastVote && pending > 0 && (
          <div className="last-choice">
            <span>Pilihan kamu di CP2: {lastVote}</span>
            <button onClick={onUndo}>Ubah</button>
          </div>
        )}
        <div className="quorum">
          <h3>Kuorum</h3>
          <div className="jurors">
            <button className="voted">
              <i>✓</i>
              <span>Juri 1</span>
            </button>
            <button onClick={onReview} title="Simulasikan suara terbelah">
              <i>{review ? "✕" : "○"}</i>
              <span>Juri 3</span>
            </button>
            <button className="you">
              <i>{lastVote ? (lastVote === "setuju" ? "✓" : "✕") : "○"}</i>
              <span>Kamu</span>
            </button>
          </div>
          <p>Butuh lebih dari setengah dari 3 juri.</p>
        </div>
        <div className="queue">
          <h3>Antrean dan hasil</h3>
          <div>
            <span className="done">CP1 · 🔒 Final</span>
            {pending > 0 && (
              <span className={lastVote ? "done" : ""}>
                CP2 ·{" "}
                {review
                  ? "⚖ Ditinjau"
                  : !online && lastVote
                    ? "◔ Antre"
                    : synced && lastVote
                      ? "✓ Terkirim"
                      : lastVote
                        ? `${lastVote === "setuju" ? "✓ Disetujui" : "✕ Ditolak"}`
                        : "◔ Menunggu juri"}
              </span>
            )}
            {pending > 0 && current === 3 && <span>CP3 · ◔ Menunggu juri</span>}
          </div>
        </div>
        <p className="chief-note">
          Mayoritas bukan keputusan akhir. Chief memeriksa bukti sebelum mengesahkan atau meninjau
          ulang.
        </p>
      </section>
    </div>
  );
}

export function FloatingRemote({ pending, open }: { pending: number; open: () => void }) {
  return (
    <button className="floating-remote" onClick={open}>
      🎮 Remote · {pending} menunggu
    </button>
  );
}
