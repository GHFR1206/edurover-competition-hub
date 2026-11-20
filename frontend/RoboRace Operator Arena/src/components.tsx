import type { ArenaData, ArenaId, QueueItem, QueueStatus } from "./data";
import { arenaIds, formatDuration } from "./data";
import { DatePicker, TimePicker } from "./schedule-pickers";

const ASSETS = "/assets/operator-arena";

export function Navbar() {
  return (
    <nav className="navbar" aria-label="Navigasi utama">
      <a className="brand" href="#top" aria-label="RoboRace">
        Robo<span>Race</span>
      </a>
      <div className="nav-menu">
        <a className="nav-active" href="#antrean">
          Antrean Arena
        </a>
      </div>
      <span className="operator-label">Operator Arena</span>
    </nav>
  );
}

export function Hero({ subtitle }: { subtitle: string }) {
  return (
    <header className="hero" id="top">
      <div className="event-row">
        <span className="live-chip">● LANGSUNG · 14 NOV 2026</span>
        <span className="series-chip">Seri Pilot</span>
      </div>
      <div className="hero-content">
        <div>
          <h1>Jaga ritme. Nyalakan arena.</h1>
          <p>{subtitle}</p>
        </div>
        <img className="hero-mascot" src={`${ASSETS}/dc500.png`} alt="Maskot RoboRace melayang" />
      </div>
    </header>
  );
}

export function ArenaTabs({
  active,
  onChange,
}: {
  active: ArenaId;
  onChange: (id: ArenaId) => void;
}) {
  return (
    <div className="arena-tabs" aria-label="Pilih arena">
      {arenaIds.map((id) => (
        <button
          className={active === id ? "chip active" : "chip"}
          onClick={() => onChange(id)}
          key={id}
        >
          Arena {id} · {id === "A" ? "SD" : id === "B" ? "SLTP" : "SLTA"}
        </button>
      ))}
    </div>
  );
}

export function RunCard({
  arena,
  onFinish,
  onIssue,
}: {
  arena: ArenaData;
  onFinish: () => void;
  onIssue: () => void;
}) {
  const run = arena.run;
  return (
    <section className="run-card reveal">
      {!run ? (
        <div className="empty-run">
          <span className="run-label">RUN ARENA</span>
          <h2>Belum ada run berjalan.</h2>
          <p>Pilih tim berstatus Siap mulai dari antrean sesi.</p>
        </div>
      ) : (
        <>
          <span className="run-label">● RUN BERJALAN · {run.startTime} WIB</span>
          <div className="run-grid">
            <div className="active-robot">
              <h2>
                {run.team} · {run.robot}
              </h2>
              <p>
                {run.school} · {run.challenge}
              </p>
              <div
                className="timer"
                aria-label={`Durasi ${formatDuration(run.elapsed)} dari 05:00`}
              >
                {formatDuration(run.elapsed)} <small>/ 05:00</small>
              </div>
            </div>
            <div className="telemetry">
              <strong>Robot kirim pencapaian</strong>
              <div className="badges">
                <span>✓ START · 0</span>
                <span>✓ CP1 · 20</span>
                <span className="pending">◔ CP2 · diterima</span>
              </div>
              <p>Paket terakhir 10:02:36 · EduRover tersambung. Keputusan poin menunggu Chief.</p>
            </div>
          </div>
          <div className="run-actions">
            <button className="button primary" onClick={onFinish}>
              Selesai
            </button>
            <button className="button secondary" onClick={onIssue}>
              Gangguan arena
            </button>
          </div>
        </>
      )}
    </section>
  );
}

const filters = ["Semua", "Panggil →", "Check-in & robot siap →", "Selesai"] as const;
export type Filter = (typeof filters)[number];

function StatusBadge({ status }: { status: QueueStatus }) {
  return (
    <span className={`status status-${status.toLowerCase().replace(" ", "-")}`}>{status}</span>
  );
}

function actionFor(status: QueueStatus) {
  if (status === "Menunggu") return "Panggil";
  if (status === "Dipanggil") return "Check-in & robot siap";
  if (status === "Siap mulai") return "Mulai";
  return null;
}

export function QueueCard({
  items,
  filter,
  onFilter,
  onAdvance,
}: {
  items: QueueItem[];
  filter: Filter;
  onFilter: (filter: Filter) => void;
  onAdvance: (item: QueueItem) => void;
}) {
  const visible = items.filter((item) => {
    if (filter === "Semua") return true;
    if (filter === "Panggil →") return item.status === "Menunggu";
    if (filter === "Check-in & robot siap →") return item.status === "Dipanggil";
    return item.status === "Selesai";
  });
  return (
    <section className="content-card reveal" id="antrean">
      <h2>Antrean sesi</h2>
      <div className="filter-row">
        {filters.map((value) => (
          <button
            key={value}
            className={filter === value ? "chip active" : "chip"}
            onClick={() => onFilter(value)}
          >
            {value}
          </button>
        ))}
      </div>
      <div className="queue-list">
        {visible.length === 0 && <p className="empty-list">Belum ada sesi pada filter ini.</p>}
        {visible.map((item) => {
          const action = actionFor(item.status);
          return (
            <article className="queue-row" key={item.id}>
              <div className="queue-info">
                <h3>
                  {item.time} · {item.team}
                  {item.robot ? ` · ${item.robot}` : ""}
                </h3>
                <p>
                  {item.school} · {item.detail}
                </p>
              </div>
              <div className="queue-meta">
                <StatusBadge status={item.status} />
                {action && (
                  <button className="button secondary" onClick={() => onAdvance(item)}>
                    {action}
                  </button>
                )}
              </div>
            </article>
          );
        })}
      </div>
    </section>
  );
}

const issueTypes = ["No-show", "DNF", "DQ", "Gangguan arena"] as const;
export type IssueType = (typeof issueTypes)[number];

export function SpecialHandling({
  selected,
  reason,
  rerunDate,
  rerunTime,
  replacement,
  sectionRef,
  onSelect,
  onReason,
  onRerunDate,
  onRerunTime,
  onReplacement,
  onSchedule,
}: {
  selected: IssueType | null;
  reason: string;
  rerunDate: string;
  rerunTime: string;
  replacement: ArenaId;
  sectionRef: React.RefObject<HTMLElement | null>;
  onSelect: (type: IssueType) => void;
  onReason: (reason: string) => void;
  onRerunDate: (date: string) => void;
  onRerunTime: (time: string) => void;
  onReplacement: (id: ArenaId) => void;
  onSchedule: () => void;
}) {
  return (
    <section className="content-card special reveal" ref={sectionRef}>
      <span className="eyebrow">PILIH STATUS, CATAT ALASAN</span>
      <h2>Penanganan khusus</h2>
      <div className="issue-row">
        {issueTypes.map((type) => (
          <button
            key={type}
            className={`button issue ${type === "DQ" ? "danger" : ""} ${selected === type ? "selected" : ""}`}
            onClick={() => onSelect(type)}
          >
            {type}
          </button>
        ))}
      </div>
      <label>
        Alasan / catatan operator
        <textarea
          value={reason}
          onChange={(e) => onReason(e.target.value)}
          placeholder="Tuliskan alasan penanganan khusus"
        />
      </label>
      <div className="field-grid">
        <label>
          Tanggal re-run
          <DatePicker value={rerunDate} onChange={onRerunDate} />
        </label>
        <label>
          Jam re-run (WIB)
          <TimePicker value={rerunTime} onChange={onRerunTime} />
        </label>
        <label>
          Arena pengganti
          <select value={replacement} onChange={(e) => onReplacement(e.target.value as ArenaId)}>
            {arenaIds.map((id) => (
              <option value={id} key={id}>
                Arena {id}
              </option>
            ))}
          </select>
        </label>
      </div>
      <button className="button primary" onClick={onSchedule}>
        Jadwalkan re-run
      </button>
    </section>
  );
}

export function MascotMessage() {
  return (
    <aside className="mascot-message">
      <img src={`${ASSETS}/17d7a.png`} alt="" />
      <strong>
        Pastikan robot benar-benar siap sebelum Mulai. Operator mengatur sesi, Chief menentukan
        keabsahan pencapaian.
      </strong>
    </aside>
  );
}

export function Footer() {
  return (
    <footer>
      <div className="footer-wave">
        <img src={`${ASSETS}/c9747.svg`} alt="" />
      </div>
      <div className="footer-content">
        <strong>RoboRace</strong>
        <span>Powered by Creativity. Driven by Technology.</span>
        <hr />
        <small>© 2026 RoboRace. All Rights Reserved.</small>
      </div>
      <div className="checker" />
    </footer>
  );
}
