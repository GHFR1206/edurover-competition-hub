import { useMemo, useState } from "react";
import { Button, Card, Chip, Field, Notice, PageShell, StatusBadge } from "./components";
import {
  initialEvents,
  initialSessions,
  initialStaff,
  reportRows,
  rulesets,
  type EventItem,
  type EventStatus,
  type Page,
  type SessionItem,
  type StaffItem,
} from "./data";

const nextStatus: Partial<Record<EventStatus, EventStatus>> = {
  Draft: "Open Registrasi",
  "Open Registrasi": "Ongoing",
  Ongoing: "Selesai",
};

export default function App() {
  const [page, setPage] = useState<Page>("events");
  const [setupEventId, setSetupEventId] = useState(1);
  const [notice, setNotice] = useState<{
    text: string;
    tone?: "success" | "warning" | "error";
  } | null>(null);
  const [events, setEvents] = useState(initialEvents);

  const notify = (text: string, tone: "success" | "warning" | "error" = "success") => {
    setNotice({ text, tone });
    window.setTimeout(() => setNotice(null), 3600);
  };

  const navigate = (target: Page) => {
    setPage(target);
    setNotice(null);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  return (
    <PageShell page={page} onNavigate={navigate}>
      {page === "events" && (
        <EventsPage
          events={events}
          setEvents={setEvents}
          navigate={navigate}
          selectSetupEvent={setSetupEventId}
          notify={notify}
        />
      )}
      {page === "setup" && (
        <SetupPage eventId={setupEventId} setEvents={setEvents} notify={notify} />
      )}
      {page === "staff" && <StaffPage notify={notify} />}
      {page === "schedule" && <SchedulePage notify={notify} />}
      {page === "reports" && <ReportsPage notify={notify} />}
      {notice && <Notice tone={notice.tone}>{notice.text}</Notice>}
    </PageShell>
  );
}

type Notify = (text: string, tone?: "success" | "warning" | "error") => void;

function EventsPage({
  events,
  setEvents,
  navigate,
  selectSetupEvent,
  notify,
}: {
  events: EventItem[];
  setEvents: React.Dispatch<React.SetStateAction<EventItem[]>>;
  navigate: (page: Page) => void;
  selectSetupEvent: (id: number) => void;
  notify: Notify;
}) {
  const [filter, setFilter] = useState<EventStatus | "Semua">("Semua");
  const [form, setForm] = useState({
    name: "RoboRace Festival Pelajar",
    location: "GOR Edukasi Nusantara",
    start: "2027-02-20",
    end: "2027-02-21",
  });
  const visible = filter === "Semua" ? events : events.filter((event) => event.status === filter);

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    if (Object.values(form).some((value) => !value.trim()))
      return notify("Semua isian event wajib dilengkapi.", "error");
    if (form.end < form.start)
      return notify("Tanggal selesai tidak boleh lebih awal dari tanggal mulai.", "error");
    setEvents((items) => [
      {
        id: Date.now(),
        name: form.name,
        detail: `${form.start} · ${form.location} · Setup belum lengkap`,
        status: "Draft",
        setupComplete: false,
      },
      ...items,
    ]);
    notify(`Event ${form.name} ditambahkan sebagai Draft.`);
  };

  const advance = (item: EventItem) => {
    if (item.status === "Draft" && !item.setupComplete)
      return notify("Setup harus lengkap sebelum membuka registrasi.", "warning");
    const next = nextStatus[item.status];
    if (!next) return;
    setEvents((items) =>
      items.map((event) => (event.id === item.id ? { ...event, status: next } : event)),
    );
    notify(`${item.name} maju ke ${next}.`);
  };

  return (
    <>
      <Card>
        <h2>Buat event</h2>
        <form onSubmit={submit}>
          <div className="form-grid two">
            <Field
              label="Nama event"
              value={form.name}
              onChange={(name) => setForm({ ...form, name })}
            />
            <Field
              label="Lokasi"
              value={form.location}
              onChange={(location) => setForm({ ...form, location })}
            />
            <Field
              label="Tanggal mulai"
              type="date"
              value={form.start}
              onChange={(start) => setForm({ ...form, start })}
            />
            <Field
              label="Tanggal selesai"
              type="date"
              value={form.end}
              onChange={(end) => setForm({ ...form, end })}
            />
          </div>
          <Button type="submit" variant="yellow">
            Buat event
          </Button>
        </form>
      </Card>
      <Card>
        <span className="eyebrow">TRANSISI SATU ARAH</span>
        <h2>Daftar Event</h2>
        <div className="chips">
          {(["Semua", "Draft", "Open Registrasi", "Ongoing", "Selesai"] as const).map((value) => (
            <Chip key={value} active={filter === value} onClick={() => setFilter(value)}>
              {value}
            </Chip>
          ))}
        </div>
        <p className="muted">
          Setiap tahap maju satu arah. Periksa setup dan penugasan sebelum melanjutkan.
        </p>
        <div className="rows">
          {visible.map((item) => (
            <div className="event-entry" key={item.id}>
              <div className="row">
                <div className="row-copy">
                  <strong>{item.name}</strong>
                  <span>{item.detail}</span>
                </div>
                <div className="row-actions">
                  <StatusBadge status={item.status} />
                  {item.status === "Selesai" ? (
                    <Button onClick={() => notify("Arsip event sedang disiapkan.")}>
                      Lihat arsip
                    </Button>
                  ) : (
                    <Button
                      onClick={() => {
                        selectSetupEvent(item.id);
                        navigate("setup");
                      }}
                    >
                      Atur setup
                    </Button>
                  )}
                </div>
              </div>
              {nextStatus[item.status] && (
                <Button onClick={() => advance(item)}>Maju ke {nextStatus[item.status]}</Button>
              )}
            </div>
          ))}
        </div>
      </Card>
    </>
  );
}

function SetupPage({
  eventId,
  setEvents,
  notify,
}: {
  eventId: number;
  setEvents: React.Dispatch<React.SetStateAction<EventItem[]>>;
  notify: Notify;
}) {
  const [step, setStep] = useState(3);
  const [categories, setCategories] = useState(["SD", "SLTP", "SLTA"]);
  const [level, setLevel] = useState("SLTP");
  const [challenges, setChallenges] = useState<
    Record<string, { name: string; duration: string; description: string }>
  >({
    SD: {
      name: "Lintasan Dasar",
      duration: "5",
      description: "Ikuti garis dan selesaikan seluruh checkpoint.",
    },
    SLTP: {
      name: "Smart Warehouse",
      duration: "6",
      description:
        "Antar paket ke zona gudang sesuai urutan. Lewati CP1, CP2, CP3, lalu capai FINISH tanpa menyentuh zona keselamatan.",
    },
    SLTA: { name: "Navigasi Otonom", duration: "8", description: "" },
  });
  const current = challenges[level];
  const challengeComplete = categories.every((category) => {
    const challenge = challenges[category];
    return challenge?.name.trim() && challenge?.duration.trim() && challenge?.description.trim();
  });
  const checks = [
    [categories.length > 0, "Kategori kompetisi sudah dipilih"],
    [true, "Ruleset tiap level sudah dikunci"],
    [
      challengeComplete,
      challengeComplete ? "Semua challenge lengkap" : "Lengkapi deskripsi challenge SLTA",
    ],
  ] as const;
  const allComplete = checks.every(([value]) => value);

  const updateChallenge = (key: keyof typeof current, value: string) => {
    setChallenges({ ...challenges, [level]: { ...current, [key]: value } });
  };

  return (
    <>
      <div className="chips setup-tabs">
        {[1, 2, 3].map((value) => (
          <Chip key={value} active={step === value} onClick={() => setStep(value)}>
            {value} · {["Kategori", "Ruleset", "Challenge"][value - 1]}
          </Chip>
        ))}
      </div>
      <Card className={step !== 1 ? "subdued" : ""}>
        <span className="eyebrow">JENJANG PESERTA</span>
        <h2>1 · Kategori</h2>
        <p className="muted">Aktifkan level yang dipertandingkan dan lengkapi setiap ruleset.</p>
        <div className="chips">
          {["SD", "SLTP", "SLTA"].map((category) => (
            <Chip
              key={category}
              active={categories.includes(category)}
              onClick={() =>
                setCategories((items) =>
                  items.includes(category)
                    ? items.filter((item) => item !== category)
                    : [...items, category],
                )
              }
            >
              ✓ {category}
            </Chip>
          ))}
        </div>
        <span className="mini-success">{categories.length} kategori aktif</span>
      </Card>
      <Card className={step !== 2 ? "subdued" : ""}>
        <span className="eyebrow">BOBOT TERKUNCI · TOTAL 100%</span>
        <h2>2 · Ruleset</h2>
        <p className="muted">
          Bobot kriteria mengikuti jenjang. Nilai manual tetap 0–20, ditambahkan ke poin robot yang
          disahkan.
        </p>
        <div className="rules">
          {rulesets.map((rule) => (
            <div className="rule" key={rule.level}>
              <span className="rule-level">▣ {rule.level}</span>
              {rule.weights.map(([name, amount]) => (
                <div key={name}>
                  <strong>{amount}</strong>
                  <span>{name}</span>
                </div>
              ))}
            </div>
          ))}
        </div>
      </Card>
      <Card className={step !== 3 ? "subdued" : ""}>
        <span className="eyebrow">PILIH LEVEL, ATUR RITME</span>
        <h2>3 · Challenge</h2>
        <div className="chips">
          {["SD", "SLTP", "SLTA"].map((item) => (
            <Chip key={item} active={level === item} onClick={() => setLevel(item)}>
              {item}
            </Chip>
          ))}
        </div>
        <div className="form-grid two">
          <Field
            label="Nama challenge"
            value={current.name}
            onChange={(value) => updateChallenge("name", value)}
          />
          <Field
            label="Batas waktu (menit)"
            value={current.duration}
            onChange={(value) => updateChallenge("duration", value)}
          />
        </div>
        <label className="field">
          <span>Deskripsi challenge</span>
          <textarea
            value={current.description}
            onChange={(event) => updateChallenge("description", event.target.value)}
          />
        </label>
        <div className="actions">
          <Button variant="primary" onClick={() => notify(`Challenge ${level} disimpan.`)}>
            Simpan challenge
          </Button>
          <Button onClick={() => notify("Draft setup berhasil disimpan.")}>Simpan draft</Button>
        </div>
      </Card>
      <Card>
        <h2>Periksa sebelum membuka</h2>
        <ul className="checklist">
          {checks.map(([done, label]) => (
            <li key={label} className={done ? "done" : ""}>
              {done ? "✓" : "○"} {label}
            </li>
          ))}
        </ul>
        {!allComplete && (
          <Notice tone="warning">
            Satu langkah lagi! Semua kategori harus punya challenge lengkap sebelum pendaftaran
            dibuka.
          </Notice>
        )}
        <Button
          variant="yellow"
          disabled={!allComplete}
          onClick={() => {
            setEvents((items) =>
              items.map((item) =>
                item.id === eventId
                  ? {
                      ...item,
                      setupComplete: true,
                      detail: item.detail.replace("Setup belum lengkap", "Setup lengkap"),
                    }
                  : item,
              ),
            );
            notify("Pendaftaran berhasil dibuka.");
          }}
        >
          Buka pendaftaran
        </Button>
      </Card>
    </>
  );
}

function StaffPage({ notify }: { notify: Notify }) {
  const [counts, setCounts] = useState({ SD: 4, SLTP: 4, SLTA: 4 });
  const [staff, setStaff] = useState<StaffItem[]>(initialStaff);
  const [form, setForm] = useState({
    name: "",
    email: "",
    role: "Juri",
    level: "SLTP",
    arena: "Arena B",
  });
  const change = (level: keyof typeof counts, delta: number) =>
    setCounts({ ...counts, [level]: Math.max(2, Math.min(7, counts[level] + delta)) });
  const invite = (event: React.FormEvent) => {
    event.preventDefault();
    if (!form.name.trim()) return notify("Nama staf wajib diisi.", "error");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
      return notify("Masukkan format email yang valid.", "error");
    setStaff((items) => [
      ...items,
      {
        id: Date.now(),
        name: form.name,
        role: form.role,
        detail: `${form.level} · ${form.arena} · Undangan terkirim`,
      },
    ]);
    notify(
      `Undangan untuk ${form.email} terkirim. Penugasan ${form.role} ${form.level} dicatat di audit log.`,
    );
  };
  return (
    <>
      <Card>
        <h2>Jumlah juri per level</h2>
        <div className="jury-grid">
          {(Object.keys(counts) as (keyof typeof counts)[]).map((level) => (
            <div className="jury" key={level}>
              <span>{level}</span>
              <div>
                <button onClick={() => change(level, -1)}>−</button>
                <strong>{counts[level]}</strong>
                <button onClick={() => change(level, 1)}>+</button>
              </div>
              <b>
                Kuorum {Math.floor(counts[level] / 2) + 1} dari {counts[level]} juri
              </b>
              <small>
                Mayoritas suara membantu review.
                <br />
                Keputusan akhir tetap pada Chief Judge.
              </small>
            </div>
          ))}
        </div>
      </Card>
      <Card>
        <h2>Penugasan aktif</h2>
        <div className="rows">
          {staff.map((item) => (
            <div className="row" key={item.id}>
              <div className="row-copy">
                <strong>
                  {item.name} · {item.role}
                </strong>
                <span>{item.detail}</span>
              </div>
              <div className="row-actions">
                <StatusBadge status="Aktif" />
                <Button onClick={() => notify(`${item.name} berhasil ditugaskan ulang.`)}>
                  Tugaskan ulang
                </Button>
              </div>
            </div>
          ))}
        </div>
      </Card>
      <Card>
        <h2>Undang staf</h2>
        <form onSubmit={invite}>
          <div className="form-grid two">
            <Field
              label="Nama staf"
              value={form.name}
              placeholder="Nama lengkap"
              onChange={(name) => setForm({ ...form, name })}
            />
            <Field
              label="Email"
              type="email"
              value={form.email}
              placeholder="staf@sekolah.contoh"
              onChange={(email) => setForm({ ...form, email })}
            />
          </div>
          <div className="chips roles">
            {["Operator Arena", "Juri", "Chief Judge"].map((role) => (
              <Chip
                key={role}
                active={form.role === role}
                onClick={() => setForm({ ...form, role })}
              >
                {role}
              </Chip>
            ))}
          </div>
          <div className="form-grid two">
            <label className="field">
              <span>Level penugasan</span>
              <select
                value={form.level}
                onChange={(e) => setForm({ ...form, level: e.target.value })}
              >
                <option>SD</option>
                <option>SLTP</option>
                <option>SLTA</option>
              </select>
            </label>
            <label className="field">
              <span>Arena</span>
              <select
                value={form.arena}
                onChange={(e) => setForm({ ...form, arena: e.target.value })}
              >
                <option>Arena A</option>
                <option>Arena B</option>
                <option>Arena C</option>
              </select>
            </label>
          </div>
          <Button type="submit" variant="yellow">
            Kirim undangan
          </Button>
        </form>
      </Card>
    </>
  );
}

function SchedulePage({ notify }: { notify: Notify }) {
  const [sessions, setSessions] = useState<SessionItem[]>(initialSessions);
  const [filter, setFilter] = useState("Semua arena");
  const [form, setForm] = useState({
    robot: "NOVA · NR-024",
    team: "SD Inovasi 3",
    arena: "Arena A",
    level: "SD",
    date: "2026-11-14",
    time: "10:15",
    duration: "15",
  });
  const visible =
    filter === "Semua arena"
      ? sessions
      : sessions.filter((item) => `${item.arena} · ${item.level}` === filter);
  const add = (event: React.FormEvent) => {
    event.preventDefault();
    const conflict = sessions.some(
      (item) =>
        item.date === form.date &&
        item.time === form.time &&
        (item.arena === form.arena || item.team.toLowerCase() === form.team.toLowerCase()),
    );
    if (conflict)
      return notify(
        "Sesi bentrok: arena atau tim yang sama sudah terjadwal pada waktu tersebut.",
        "error",
      );
    setSessions((items) => [
      ...items,
      { id: Date.now(), ...form, duration: Number(form.duration), status: "Terjadwal" },
    ]);
    notify(
      `Sesi ${form.robot.split(" · ")[0]} ditambahkan pada ${form.time} WIB. Tidak ada bentrok arena atau tim.`,
    );
  };
  return (
    <>
      <Card>
        <h2>Tambah sesi</h2>
        <form onSubmit={add}>
          <div className="form-grid two">
            <Field
              label="Tim / robot"
              value={form.robot}
              onChange={(robot) => setForm({ ...form, robot })}
            />
            <label className="field">
              <span>Arena</span>
              <select
                value={`${form.arena}|${form.level}`}
                onChange={(e) => {
                  const [arena, level] = e.target.value.split("|");
                  setForm({ ...form, arena, level });
                }}
              >
                <option value="Arena A|SD">Arena A · SD</option>
                <option value="Arena B|SLTP">Arena B · SLTP</option>
                <option value="Arena C|SLTA">Arena C · SLTA</option>
              </select>
            </label>
          </div>
          <div className="form-grid three">
            <Field
              label="Tanggal"
              type="date"
              value={form.date}
              onChange={(date) => setForm({ ...form, date })}
            />
            <Field
              label="Waktu mulai (WIB)"
              type="time"
              value={form.time}
              onChange={(time) => setForm({ ...form, time })}
            />
            <Field
              label="Durasi slot (menit)"
              type="number"
              value={form.duration}
              onChange={(duration) => setForm({ ...form, duration })}
            />
          </div>
          <Field
            label="Nama tim"
            value={form.team}
            onChange={(team) => setForm({ ...form, team })}
          />
          <Button type="submit" variant="yellow">
            Tambah sesi
          </Button>
        </form>
      </Card>
      <div className="chips">
        {["Semua arena", "Arena A · SD", "Arena B · SLTP", "Arena C · SLTA"].map((value) => (
          <Chip key={value} active={filter === value} onClick={() => setFilter(value)}>
            {value}
          </Chip>
        ))}
      </div>
      <Card>
        <h2>Sesi hari ini</h2>
        <div className="rows">
          {visible.map((item) => (
            <div className="row" key={item.id}>
              <div className="row-copy">
                <strong>
                  {item.time} · {item.robot}
                </strong>
                <span>
                  {item.arena} · {item.level} · {item.duration} menit · {item.team}
                </span>
              </div>
              <div className="row-actions">
                <StatusBadge status={item.status} />
                <Button
                  onClick={() =>
                    notify(
                      `${item.status === "Terjadwal" ? "Editor" : "Detail"} sesi ${item.robot} dibuka.`,
                    )
                  }
                >
                  {item.status === "Terjadwal" ? "Ubah sesi" : "Lihat sesi"}
                </Button>
              </div>
            </div>
          ))}
        </div>
      </Card>
    </>
  );
}

function ReportsPage({ notify }: { notify: Notify }) {
  const [exports, setExports] = useState<string[]>([]);
  const exportReport = (name: string, official = true) => {
    if (!official) return notify(`Hasil ${name} belum resmi dan belum dapat diekspor.`, "warning");
    setExports((items) => [
      ...items,
      `${name} · ${new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })}`,
    ]);
    notify(`Rekap ${name} disiapkan dan ekspor dicatat.`);
  };
  const stats = useMemo(
    () => [
      ["TIM TERDAFTAR", "48", "44 terverifikasi · 4 ditinjau"],
      ["SEKOLAH", "24", "8 SD · 10 SMP · 6 SMA/SMK"],
      ["RUN SELESAI", "36", "Dari 48 run terjadwal"],
    ],
    [],
  );
  return (
    <>
      <div className="stats">
        {stats.map(([label, value, detail]) => (
          <Card key={label} className="stat">
            <span className="eyebrow">{label}</span>
            <strong>{value}</strong>
            <small>{detail}</small>
            <i />
          </Card>
        ))}
      </div>
      <Card>
        <h2>Daftar tim & sekolah</h2>
        <div className="rows">
          {reportRows.map(([name, detail]) => (
            <div className="row" key={name}>
              <div className="row-copy">
                <strong>{name}</strong>
                <span>{detail}</span>
              </div>
              <Button onClick={() => exportReport(name)}>Ekspor</Button>
            </div>
          ))}
        </div>
      </Card>
      <Card>
        <h2>Hasil resmi</h2>
        <div className="rows">
          <div className="row">
            <div className="row-copy">
              <strong>SLTP · Hasil sudah dipublikasikan</strong>
              <span>Disahkan Bima Pradana · 14 November 2026, 11:20 WIB · PDF</span>
            </div>
            <div className="row-actions">
              <StatusBadge status="✓ Resmi" />
              <Button onClick={() => exportReport("hasil SLTP")}>Ekspor</Button>
            </div>
          </div>
          {["SD", "SLTA"].map((level) => (
            <div className="event-entry" key={level}>
              <div className="row">
                <div className="row-copy">
                  <strong>{level} · Menunggu publikasi</strong>
                  <span>
                    {level === "SD"
                      ? "Penilaian masih ditinjau oleh Chief Judge"
                      : "Sesi kompetisi masih berlangsung"}
                  </span>
                </div>
                <StatusBadge status="◔ Sementara" />
              </div>
              <Button onClick={() => exportReport(level, false)}>Ekspor {level}</Button>
            </div>
          ))}
        </div>
      </Card>
      {exports.length > 0 && <p className="audit">Ekspor tercatat: {exports.join(" · ")}</p>}
    </>
  );
}
