import { useMemo, useState } from "react";
import {
  A,
  Card,
  Footer,
  Header,
  LevelChips,
  LiveBadge,
  Mascot,
  PageHero,
  Toast,
} from "./components";
import { archives, schedule, teams, type Level, type Page } from "./data";

type Props = { page: Page; go: (page: Page) => void };

const statusClass = (status: string) => status.toLowerCase().replaceAll(" ", "-");

export function HomePage({ page, go, onChampion }: Props & { onChampion: () => void }) {
  return (
    <div className="app home-page">
      <Header page={page} go={go} />
      <section className="home-hero">
        <img className="cloud cloud-a" src={`${A}/53c8e.svg`} alt="" />
        <img className="cloud cloud-b" src={`${A}/53c8e.svg`} alt="" />
        <div className="home-copy">
          <LiveBadge />
          <h1>
            Lintasan menyala,
            <br />
            <em>robotmu siap</em>
            <br />
            <em>melesat.</em>
          </h1>
          <p>
            Dari ruang kelas ke garis finis. Rakit ide, uji keberanian, dan rayakan setiap
            pencapaian robot sekolahmu.
          </p>
          <div className="hero-actions">
            <button className="purple-btn" onClick={() => go("leaderboard")}>
              Lihat leaderboard
            </button>
            <button className="gold-btn" onClick={() => go("event")}>
              Daftar sekolah
            </button>
          </div>
        </div>
        <Mascot src="74589.png" className="home-mascot" />
      </section>
      <main>
        <section className="stats-grid">
          {[
            ["SEKOLAH TERDAFTAR", "48", "Dari 12 wilayah hebat"],
            ["TIM SIAP BERTANDING", "36", "Semua kategori lomba"],
            ["EVENT BERJALAN", "01", "Seri Pilot resmi dimulai"],
          ].map(([label, value, note], i) => (
            <Card key={label} delay={i * 100}>
              <small>{label}</small>
              <strong>{value}</strong>
              <p>{note}</p>
            </Card>
          ))}
        </section>
        <section className="section-block">
          <h2>Pilih arena petualanganmu</h2>
          <p className="sub">Setiap tim bebas memilih dari tiga level yang seru.</p>
          <div className="event-grid">
            <EventCards go={go} />
          </div>
        </section>
      </main>
      <section className="home-leader">
        <div className="section-inner">
          <span className="eyebrow">✦ PAPAN PERINGKAT</span>
          <h2>Leaderboard</h2>
          {teams.SLTP.map((team, i) => (
            <button className="rank-row" key={team.name} onClick={i === 0 ? onChampion : undefined}>
              <b>{team.rank}</b>
              <div>
                <strong>{team.name}</strong>
                <small>
                  {team.school} · {team.code}
                </small>
              </div>
              <span className={`status ${statusClass(team.status)}`}>✓ {team.status}</span>
              <em>{team.score}</em>
            </button>
          ))}
          <button className="gold-btn mini" onClick={() => go("leaderboard")}>
            Buka leaderboard lengkap
          </button>
        </div>
      </section>
      <section className="mini-results">
        <LiveBadge />
        <h2>Hasil Resmi</h2>
        <p>Smart Warehouse Challenge · Batas waktu 6 menit</p>
        <Mascot src="786e4.png" />
        <div className="mini-podium">
          <div>
            <b>2</b>
            <strong>VEGA</strong>
            <small>108 poin</small>
          </div>
          <button onClick={onChampion}>
            <span>JUARA 1</span>
            <b>1</b>
            <strong>TITAN</strong>
            <small>112 poin</small>
          </button>
          <div>
            <b>3</b>
            <strong>Belum ditetapkan</strong>
          </div>
        </div>
        <button className="outline-btn" onClick={() => go("hasil")}>
          Lihat hasil resmi →
        </button>
      </section>
      <section className="home-cta">
        <div>
          <small>SIAP IKUT?</small>
          <h2>Tertarik Ikut Bertanding?</h2>
          <p>Jangan lewatkan kesempatan untuk belajar dan berkompetisi.</p>
          <button className="gold-btn">Daftarkan timmu</button>
        </div>
        <Mascot src="e638f.png" />
      </section>
      <Footer />
    </div>
  );
}

function EventCards({ go }: { go: (page: Page) => void }) {
  return (
    <>
      <Card>
        <small>14–15 NOVEMBER 2026</small>
        <h3>RoboRace Cup 2026 (Seri Pilot)</h3>
        <span className="yellow-pill">● Sedang berlangsung</span>
        <p>GOR Edukasi Nusantara · 48 tim · 3 kategori</p>
        <p className="muted">
          Pendaftaran ditutup. Dukung tim favoritmu dan ikuti setiap pencapaiannya.
        </p>
        <button className="purple-btn" onClick={() => go("jadwal")}>
          Lihat jadwal
        </button>
      </Card>
      <Card delay={100}>
        <small>PENDAFTARAN TERBUKA</small>
        <h3>RoboRace Regional Seri 2</h3>
        <span className="yellow-pill">Hingga 30 Desember 2026</span>
        <p>Babak regional · SD, SLTP, SLTA</p>
        <p className="muted">Satu tim, satu robot, ribuan kemungkinan.</p>
        <button className="gold-btn">Daftar tim</button>
      </Card>
    </>
  );
}

export function EventPage({ page, go }: Props) {
  return (
    <div className="app lavender-page">
      <Header page={page} go={go} />
      <PageHero
        title="Arena untuk ide besarmu"
        description="Temukan tantangan yang pas untuk tim sekolahmu."
        mascot="dc500.png"
      />
      <main className="wide-main">
        <div className="event-grid">
          <EventCards go={go} />
        </div>
        <Card className="levels-card">
          <h2>Tiga level. Tiga petualangan.</h2>
          {[
            [
              "SD · Color Delivery & Adventure",
              "Antar warna ke tujuan, susuri jalur, dan tuntaskan misi pertamamu.",
              "05 menit",
            ],
            [
              "SLTP · Smart Warehouse",
              "Robot pengelola gudang: bawa paket melewati CP1, CP2, CP3 hingga FINISH.",
              "06 menit",
            ],
            [
              "SLTA · Disaster Response",
              "Navigasi zona bencana dan selesaikan misi penyelamatan secara otonom.",
              "08 menit",
            ],
          ].map(([title, desc, time]) => (
            <div className="level-line" key={title}>
              <div>
                <strong>{title}</strong>
                <p>{desc}</p>
              </div>
              <b>{time}</b>
            </div>
          ))}
        </Card>
        <div className="mascot-note">
          <Mascot src="17d7a.png" />
          Pilih level sesuai jenjang sekolah. Tim terverifikasi akan mendapat robot_code dan API
          key.
        </div>
      </main>
      <Footer />
    </div>
  );
}

export function SchedulePage({ page, go }: Props) {
  const [level, setLevel] = useState<Level>("SLTP");
  const rows = schedule.filter((item) => item.level === level);
  return (
    <div className="app lavender-page">
      <Header page={page} go={go} />
      <PageHero
        title="Hari ini, giliran siapa?"
        description="Sabtu, 14 November 2026 · Semua waktu dalam WIB"
        mascot="17d7a.png"
      />
      <main className="wide-main">
        <LevelChips active={level} setActive={setLevel} />
        <section className="stats-grid schedule-stats">
          {[
            ["SESI HARI INI", "24", "08.00–16.30 WIB"],
            ["ARENA AKTIF", "03", "A · B · C"],
            ["SESI BERIKUTNYA", rows[0]?.time || "—", `${rows[0]?.team || "NOVA"} · Arena A`],
          ].map(([a, b, c]) => (
            <Card key={a}>
              <small>{a}</small>
              <strong>{b}</strong>
              <p>{c}</p>
            </Card>
          ))}
        </section>
        <Card className="schedule-card">
          <small>JAM · TIM · ROBOT · STATUS</small>
          <h2>Jadwal arena</h2>
          {rows.map((item, i) => (
            <div
              className="schedule-row reveal"
              style={{ animationDelay: `${i * 80}ms` }}
              key={item.time}
            >
              <div>
                <strong>
                  {item.time} · {item.arena} · {item.team}
                </strong>
                <p>
                  {item.school} · {item.level} · {item.code} · Sesi penyisihan
                </p>
              </div>
              <span className={`status ${statusClass(item.status)}`}>{item.status}</span>
            </div>
          ))}
        </Card>
        <div className="notice">
          Jadwal dapat berubah jika ada gangguan arena. Pantau informasi dari operator sebelum
          check-in.
        </div>
      </main>
      <Footer />
    </div>
  );
}

export function LeaderboardPage({ page, go, onChampion }: Props & { onChampion: () => void }) {
  const [level, setLevel] = useState<Level>("SD");
  return (
    <div className="app leaderboard-page">
      <Header page={page} go={go} dark />
      <main className="leader-main">
        <PageHero
          title="Leaderboard"
          description="RoboRace Cup 2026 (Seri Pilot) · Skor per level, transparan di setiap langkah."
          mascot="786e4.png"
        />
        <Card className="leader-message">
          <span className="yellow-pill">✦ PAPAN PERINGKAT LANGSUNG</span>
          <h2>Setiap checkpoint, satu langkah lebih jauh.</h2>
          <p>
            ✓ Resmi setelah Chief Judge memverifikasi penilaian. Skor lainnya tetap ◷ Sementara.
          </p>
          <div className="planet">◯</div>
        </Card>
        <LevelChips active={level} setActive={setLevel} dark />
        {teams[level].map((team, i) => (
          <button
            className={`leader-card reveal ${i === 0 ? "winner" : ""}`}
            style={{ animationDelay: `${i * 100}ms` }}
            key={team.name}
            onClick={i === 0 ? onChampion : undefined}
          >
            <span className="rank-shield">{team.rank}</span>
            <div>
              <h3>
                {team.name} <small>{team.code}</small>
              </h3>
              <p>{team.school}</p>
              <small>
                {team.time} · Poin robot {team.score === "—" ? "belum tersedia" : "100"}
              </small>
            </div>
            <span className={`status ${statusClass(team.status)}`}>
              {team.status === "Resmi" ? "✓" : "◷"} {team.status}
            </span>
            <strong>
              {team.score}
              <small>POIN</small>
            </strong>
          </button>
        ))}
      </main>
      <Footer />
    </div>
  );
}

function Podium({ current, onChampion }: { current: Level; onChampion: () => void }) {
  const rows = teams[current];
  return (
    <div className="podium">
      <div className="podium-place second">
        <b>2</b>
        <strong>{rows[1].name}</strong>
        <span>{rows[1].score} poin</span>
      </div>
      <button className="podium-place first" onClick={onChampion}>
        <small>🏆 JUARA 1</small>
        <b>1</b>
        <strong>{rows[0].name}</strong>
        <span>
          {rows[0].score} poin · {rows[0].time}
        </span>
      </button>
      <div className="podium-place third">
        <b>3</b>
        <strong>{rows[2].name}</strong>
        <span>
          {rows[2].status === "Sementara" ? "Menunggu hasil valid" : `${rows[2].score} poin`}
        </span>
      </div>
    </div>
  );
}

export function ResultsPage({
  page,
  go,
  expanded,
  onExpand,
}: Props & { expanded: boolean; onExpand: () => void }) {
  const [level, setLevel] = useState<Level>("SLTP");
  const [toast, setToast] = useState(false);
  const download = () => {
    setToast(true);
    window.setTimeout(() => setToast(false), 2200);
  };
  return (
    <div className="app results-page">
      <Header page={page} go={go} />
      <main className="results-main">
        <img className="sunburst" src={`${A}/4a0a9.svg`} alt="" />
        <section className="results-title">
          <LiveBadge />
          <h1>Hasil Resmi</h1>
          <p>Smart Warehouse Challenge · Batas waktu 6 menit</p>
          <Mascot src="786e4.png" />
        </section>
        <Podium current={level} onChampion={onExpand} />
        <button className="gold-btn download" onClick={download}>
          Unduh rekap resmi
        </button>
        <div className="dash" />
        <div className="result-filter">
          <LevelChips active={level} setActive={setLevel} />
          <span>✓ {level} · DIPUBLIKASIKAN</span>
        </div>
        <Card className="results-table">
          <div className="table-head">
            <span>Rank</span>
            <span>Tim · Sekolah</span>
            <span>Total skor</span>
            <span>Waktu</span>
          </div>
          {teams[level].map((team) => (
            <div key={team.name} className="result-row">
              <b>{team.rank}</b>
              <div>
                <strong>{team.name}</strong>
                <small>
                  {team.school} · {team.code}
                </small>
              </div>
              <strong>{team.score}</strong>
              <span>{team.time}</span>
              <button onClick={team.rank === 1 ? onExpand : undefined}>
                {team.rank === 1 && expanded ? "⌃" : "⌄"}
              </button>
              {team.rank === 1 && expanded && (
                <ExpandedDetails team={team.name} score={team.score} />
              )}
            </div>
          ))}
          <small className="table-note">
            Nilai juri 0–20 bersifat aditif terhadap skor robot. Ketuk baris untuk rincian
            checkpoint.
          </small>
        </Card>
        <div className="notice">
          ◷ SLTA · Hasil belum dipublikasikan — juri masih menuntaskan penilaian.
        </div>
      </main>
      {toast && <Toast>Rekap resmi sedang disiapkan.</Toast>}
      <Footer />
    </div>
  );
}

function ExpandedDetails({ team, score }: { team: string; score: string }) {
  return (
    <div className="expanded">
      <h3>Score Breakdown · {team}</h3>
      <div className="team-summary">
        <Mascot src="786e4.png" />
        <div>
          <strong>{team}</strong>
          <small>SMP Garuda A · NR-114</small>
        </div>
        <b>{score}</b>
      </div>
      <div className="reference">Referensi konfigurasi · nilai parameter belum tersedia</div>
      <h4>
        Task Completion <small>Maksimal 70</small>
      </h4>
      {["Start", "CP1", "CP2", "CP3", "Finish"].map((name, i) => (
        <div className="checkpoint" key={name}>
          <span>{i === 4 ? "✓" : i + 1}</span>
          <strong>{name}</strong>
          <small>Status — Poin — Waktu {i === 4 ? "02:14" : "—"}</small>
        </div>
      ))}
      {["Navigation / Accuracy", "Efficiency / Time", "Autonomy / Innovation"].map((name) => (
        <div className="detail-bar" key={name}>
          <strong>{name}</strong>
          <span>⌄</span>
        </div>
      ))}
      <h4>Bonus & Penalti</h4>
      <div className="calculation">
        <p>
          Bonus total <span>—</span>
        </p>
        <p>
          Penalti total <span>—</span>
        </p>
        <p>
          Subtotal sementara <span>—</span>
        </p>
        <strong>
          Total skor terbit <b>{score}</b>
        </strong>
      </div>
    </div>
  );
}

export function ArchivePage({ page, go }: Props) {
  const [year, setYear] = useState(2025);
  const [toast, setToast] = useState(false);
  const cards = useMemo(() => archives.filter((item) => item.year === year), [year]);
  const download = () => {
    setToast(true);
    window.setTimeout(() => setToast(false), 2200);
  };
  return (
    <div className="app archive-page">
      <Header page={page} go={go} />
      <PageHero
        title="Jejak juara, dari tahun ke tahun"
        description="Buka kembali pencapaian hebat dan hasil resmi setiap musim."
        mascot="e638f.png"
      />
      <main className="wide-main archive-main">
        <div className="chips year-chips">
          {[2026, 2025, 2024].map((value) => (
            <button
              className={year === value ? "active" : ""}
              onClick={() => setYear(value)}
              key={value}
            >
              {value}
            </button>
          ))}
        </div>
        {cards.map((archive, i) => (
          <Card key={archive.title} delay={i * 100} className="archive-card">
            <small>
              {archive.year === 2025
                ? "15–16 NOVEMBER 2025 · SELESAI"
                : `MUSIM ${archive.year} · SELESAI`}
            </small>
            <h2>{archive.title}</h2>
            <span className="status resmi">✓ Arsip resmi</span>
            <p className="muted">{archive.place}</p>
            {archive.teams.map(([team, score]) => (
              <div className="archive-row" key={team}>
                <div>
                  <strong>🏅 {team}</strong>
                  <small>Juara 1 · Hasil terverifikasi</small>
                </div>
                <b>{score}</b>
                <span className="status resmi">✓ Resmi</span>
              </div>
            ))}
            <div className="archive-actions">
              <button className="gold-btn" onClick={download}>
                Unduh rekap
              </button>
              <button className="outline-btn" onClick={() => go("hasil")}>
                Lihat hasil lengkap
              </button>
            </div>
          </Card>
        ))}
        <div className="mascot-note">
          <Mascot src="e638f.png" />
          Setiap musim punya cerita. Hasil arsip tidak berubah tanpa koreksi resmi dengan alasan
          yang tercatat.
        </div>
      </main>
      {toast && <Toast>Rekap arsip {year} sedang diunduh.</Toast>}
      <Footer />
    </div>
  );
}
