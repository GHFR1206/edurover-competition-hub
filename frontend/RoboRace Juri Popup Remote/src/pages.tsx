import { useState } from "react";
import { Chip, Footer, PageHero } from "./components";
import { leaderboard, teams, type TeamKey } from "./data";

export function RemotePage({ vote }: { vote: "setuju" | "tolak" | null }) {
  const [team, setTeam] = useState<TeamKey>("ORION");
  const data = teams[team];
  return (
    <div className="page light">
      <PageHero />
      <main className="page-content remote-content">
        <div className="team-filters">
          {(Object.keys(teams) as TeamKey[]).map((key) => (
            <Chip key={key} active={team === key} onClick={() => setTeam(key)}>
              {teams[key].label}
            </Chip>
          ))}
        </div>
        <section className="card achievement-card">
          <code>{data.quorum}</code>
          <h2>Pencapaian dari robot</h2>
          <p className="muted">
            Nilai setiap pencapaian berdasarkan bukti arena. Simulasi ini menampilkan suara remote,
            bukan pengesahan poin.
          </p>
          <div className="checkpoints">
            {data.checkpoints.map((item, index) => {
              const changed = team === "ORION" && item.id === "CP2" && vote;
              const votes = changed
                ? vote === "setuju"
                  ? "4 setuju · 1 tolak"
                  : "3 setuju · 2 tolak"
                : item.votes;
              return (
                <article
                  className="checkpoint"
                  key={item.id}
                  style={{ animationDelay: `${index * 60}ms` }}
                >
                  <div>
                    <h3>
                      {item.id} · {item.points} poin
                    </h3>
                    <span>{votes}</span>
                  </div>
                  <em className={item.tone}>
                    {changed ? "Mayoritas · menunggu Chief" : item.status}
                  </em>
                  <div className="progress">
                    <i
                      style={{
                        width: `${changed ? Math.min(item.progress + 12, 100) : item.progress}%`,
                      }}
                    />
                  </div>
                </article>
              );
            })}
          </div>
        </section>
        <div className="yellow-note">
          CP2 mendapat mayoritas 3 dari 4 suara. Poin belum masuk total sampai Chief Judge mengambil
          keputusan.
        </div>
        <section className="card vote-record">
          <h2>Suaramu tercatat</h2>
          <p>
            {vote
              ? `CP2 · ${vote === "setuju" ? "Setuju" : "Tolak"} · Sari Puspita · 10:02:41 WIB`
              : "Belum ada suara baru dari kamu."}
          </p>
          <small>
            Jejak suara menyimpan pelaku, waktu, objek pencapaian, dan perubahan untuk audit.
          </small>
          {vote && <em>✓ Simulasi suara tersimpan</em>}
        </section>
        <div className="mascot-message">
          <img src="/assets/d8b40.png" alt="" />
          <b>
            Mayoritas bukan keputusan akhir. Chief akan memeriksa bukti sebelum mengesahkan atau
            meninjau ulang.
          </b>
        </div>
      </main>
      <Footer />
    </div>
  );
}

const ManualHeader = ({ children }: { children: React.ReactNode }) => (
  <>
    <PageHero kind="manual" />
    <div className="manual-space">{children}</div>
  </>
);

function AuditAndRecap() {
  return (
    <>
      <section className="audit">
        <h3>Riwayat perubahan</h3>
        <div>
          {["Juri", "Waktu", "Sebelum", "Sesudah", "Alasan"].map((x) => (
            <span key={x}>
              <b>{x}</b>—
            </span>
          ))}
        </div>
        <p>Riwayat belum tersedia.</p>
      </section>
      <section className="card recap">
        <h2>Rekap panel SLTP</h2>
        <div>
          <span>
            <b>TITAN · 3 juri selesai</b>
            <small>Nilai 10, 12, 14 · rata-rata 12 · poin robot 100</small>
          </span>
          <strong>112</strong>
          <em>✓ Resmi</em>
        </div>
        <div>
          <span>
            <b>VEGA · 3 juri selesai</b>
            <small>Nilai 6, 8, 10 · rata-rata 8 · poin robot 100</small>
          </span>
          <strong>108</strong>
          <em>✓ Resmi</em>
        </div>
      </section>
    </>
  );
}

function TeamSummary({ titan = false, onClick }: { titan?: boolean; onClick?: () => void }) {
  return (
    <article className="card manual-team" onClick={onClick}>
      <div className="team-head">
        <span>
          <code>{titan ? "SMP GARUDA A · TERKUNCI" : "SD INOVASI 3 · DRAFT"}</code>
          <h2>{titan ? "TITAN · NR-114" : "NOVA · NR-024"}</h2>
        </span>
        <em className={titan ? "submitted" : ""}>{titan ? "✓ Sudah disubmit" : "Draft"}</em>
      </div>
      <div className="meta-chips">
        <span>Match — · Arena —</span>
        <span>Level {titan ? "SMP" : "SD"}</span>
        <span>Juri Sari Puspita</span>
      </div>
      <div className="legacy">
        <span>
          <b>Legacy manual</b>
          <strong>{titan ? "12" : "14"} / 20</strong>
          <small>Konfigurasi lama · tidak termasuk parameter baru</small>
        </span>
        <span>
          <b>Catatan penilaian</b>
          <p>
            {titan
              ? "Keamanan baik, pergerakan stabil. Masih ada jeda pada pergantian misi."
              : "Misi selesai konsisten. Akurasi peletakan paket perlu ditingkatkan."}
          </p>
        </span>
      </div>
      <div className="evidence">
        <span>{titan ? "bukti-titan.mp4 · 8,4 MB" : "bukti-nova.jpg · 1,2 MB"}</span>
        {!titan && <button>Unggah bukti</button>}
      </div>
      {!titan && (
        <p className="preview-copy">
          Parameter manual tersedia pada halaman detail. Klik kartu ini untuk melanjutkan penilaian.
        </p>
      )}
      {titan && <SummaryBlocks />}
    </article>
  );
}

function SummaryBlocks() {
  return (
    <div className="summary-grid">
      {["Bonus", "Penalty", "Total parameter", "Final"].map((x) => (
        <span key={x}>
          <b>{x}</b>
          <strong>—</strong>
          <small>Belum dapat dihitung sebelum konfigurasi lengkap.</small>
        </span>
      ))}
    </div>
  );
}

export function ManualPage({ notify }: { notify: (text: string) => void }) {
  const [filter, setFilter] = useState("Semua");
  const [detail, setDetail] = useState<"nova" | "titan" | null>(null);
  const [file, setFile] = useState("bukti-nova.jpg · 1,2 MB");
  if (detail)
    return (
      <ManualDetail
        titan={detail === "titan"}
        back={() => setDetail(null)}
        notify={notify}
        file={file}
        setFile={setFile}
      />
    );
  return (
    <div className="page light">
      <ManualHeader>
        <div className="filters">
          {["Semua", "Draft", "Sudah disubmit"].map((x) => (
            <Chip key={x} active={filter === x} onClick={() => setFilter(x)}>
              {x}
            </Chip>
          ))}
        </div>
        <section className="card guide">
          {[
            [
              "Baca dulu sebelum mulai",
              "Gunakan kartu di bawah untuk tim yang sedang dinilai. Isi kolom yang kosong, unggah bukti jika perlu, lalu simpan draft sebelum submit.",
            ],
            [
              "Isi urut dari atas ke bawah",
              "Lihat informasi tim, isi nilai manual, tambahkan catatan opsional, lalu masukkan bonus/penalty jika ada.",
            ],
            [
              "Jangan lupa bukti pendukung",
              "Unggah foto atau video jika ada bukti yang membantu penilaian. Jika tidak ada, biarkan kosong.",
            ],
            [
              "Submit hanya saat sudah lengkap",
              "Jika ada nilai kosong atau belum jelas, simpan dulu sebagai draft. Submit hanya ketika semua bagian yang perlu sudah terisi.",
            ],
          ].map((x, i) => (
            <div key={x[0]}>
              <i>{i + 1}</i>
              <span>
                <b>{x[0]}</b>
                <p>{x[1]}</p>
              </span>
            </div>
          ))}
        </section>
        <div className="manual-grid">
          {filter !== "Sudah disubmit" && <TeamSummary onClick={() => setDetail("nova")} />}
          {filter !== "Draft" && <TeamSummary titan onClick={() => setDetail("titan")} />}
        </div>
        <section className="card recap standalone">
          <h2>Rekap panel SLTP</h2>
          <div>
            <span>
              <b>TITAN · 3 juri selesai</b>
              <small>Nilai 10, 12, 14 · rata-rata 12 · poin robot 100</small>
            </span>
            <strong>112</strong>
            <em>✓ Resmi</em>
          </div>
          <div>
            <span>
              <b>VEGA · 3 juri selesai</b>
              <small>Nilai 6, 8, 10 · rata-rata 8 · poin robot 100</small>
            </span>
            <strong>108</strong>
            <em>✓ Resmi</em>
          </div>
        </section>
      </ManualHeader>
      <Footer />
    </div>
  );
}

function ManualDetail({
  titan,
  back,
  notify,
  file,
  setFile,
}: {
  titan: boolean;
  back: () => void;
  notify: (x: string) => void;
  file: string;
  setFile: (x: string) => void;
}) {
  const [scores, setScores] = useState({ nav: "", efficiency: "" });
  const clamp = (key: "nav" | "efficiency", max: number, value: string) =>
    setScores((s) => ({
      ...s,
      [key]: value === "" ? "" : String(Math.min(max, Math.max(0, Number(value)))),
    }));
  return (
    <div className="page light">
      <ManualHeader>
        <div className="filters">
          <Chip onClick={back}>← Kembali</Chip>
          <Chip active>{titan ? "Sudah disubmit" : "Draft"}</Chip>
        </div>
        <section className="card detail-card">
          <TeamSummary titan={titan} />
          {!titan && (
            <div className="parameters">
              <h3>Parameter manual</h3>
              <p>
                Batas masing-masing parameter menyesuaikan level SD. Nilai global tidak terbatas
                100.
              </p>
              <Parameter title="Task Completion" max={60} disabled />
              <Parameter
                title="Navigation / Accuracy"
                max={25}
                value={scores.nav}
                setValue={(v) => clamp("nav", 25, v)}
              />
              <Parameter
                title="Efficiency / Time"
                max={15}
                value={scores.efficiency}
                setValue={(v) => clamp("efficiency", 15, v)}
              />
              <SummaryBlocks />
              <div className="form-actions">
                <button onClick={() => notify("Draft tersimpan.")}>Simpan draft</button>
                <button disabled>Submit Scores</button>
                <span>Lengkapi parameter wajib dan konfigurasi sebelum mengirim</span>
              </div>
              <label className="upload">
                Unggah bukti
                <input type="file" onChange={(e) => setFile(e.target.files?.[0]?.name || file)} />
                <span>{file}</span>
              </label>
            </div>
          )}
          {titan && (
            <button className="locked" disabled>
              Kunci Chief Judge
            </button>
          )}
          <AuditAndRecap />
        </section>
      </ManualHeader>
      <Footer />
    </div>
  );
}

function Parameter({
  title,
  max,
  disabled,
  value,
  setValue,
}: {
  title: string;
  max: number;
  disabled?: boolean;
  value?: string;
  setValue?: (v: string) => void;
}) {
  return (
    <div className="parameter">
      <div>
        <b>{title}</b>
        {disabled ? (
          <em>Sumber menunggu konfigurasi</em>
        ) : (
          <input
            type="number"
            min="0"
            max={max}
            value={value}
            onChange={(e) => setValue?.(e.target.value)}
          />
        )}
        <span>Batas {max}</span>
      </div>
      <p>
        {disabled
          ? "Konfirmasi checkpoint — konfigurasi Task Completion belum tersedia."
          : "Ketepatan lintasan, akurasi peletakan, dan konsistensi pergerakan."}
      </p>
      {!disabled && (
        <>
          <label>
            Catatan parameter (opsional)
            <textarea placeholder="Masukkan catatan opsional untuk setiap parameter." />
          </label>
          <label>
            Bukti parameter (opsional)
            <input
              className="file-field"
              placeholder="Unggah bukti opsional untuk mendukung penilaian."
            />
          </label>
        </>
      )}
    </div>
  );
}

export function LeaderboardPage() {
  const [level, setLevel] = useState<keyof typeof leaderboard>("SLTP");
  return (
    <div className="page leaderboard">
      <header className="leader-hero">
        <div className="event-row">
          <span>● LANGSUNG · 14 NOV 2026</span>
          <b>Seri Pilot</b>
        </div>
        <h1>Leaderboard</h1>
        <p>RoboRace Cup 2026 (Seri Pilot) · Skor per level, transparan di setiap langkah.</p>
        <img src="/assets/786e4.png" alt="Maskot RoboRace" />
      </header>
      <main className="leader-content">
        <section className="sky-card">
          <div>
            <em>✦ PAPAN PERINGKAT LANGSUNG</em>
            <h2>Setiap checkpoint, satu langkah lebih jauh.</h2>
            <p>
              ✓ Resmi setelah Chief Judge memverifikasi penilaian. Skor lainnya tetap ◔ Sementara.
            </p>
          </div>
          <div className="planet">
            <img src="/assets/bd792.svg" alt="" />
            <img src="/assets/bb4de.svg" alt="" />
            <i>✦</i>
          </div>
        </section>
        <img className="circuit" src="/assets/b5eb7.svg" alt="" />
        <div className="level-filters">
          {(["SD", "SLTP", "SLTA"] as const).map((x) => (
            <Chip key={x} active={level === x} onClick={() => setLevel(x)}>
              {x}
            </Chip>
          ))}
        </div>
        <div className="rankings">
          {leaderboard[level].map((row, i) => (
            <article key={row[0]} className={i === 0 ? "first" : ""}>
              <div className="hex">{i + 1}</div>
              <div className="rank-team">
                <h3>
                  {row[0]} <code>{row[1]}</code>
                </h3>
                <p>{row[2]}</p>
                <small>
                  {row[3] === "—"
                    ? "Belum tampil · Poin robot belum tersedia"
                    : `${i ? "02:28" : "02:14"} · Poin robot 100`}
                </small>
              </div>
              <em className={row[4].includes("Resmi") ? "official" : ""}>{row[4]}</em>
              <strong>
                {row[3]}
                <small>POIN</small>
              </strong>
            </article>
          ))}
        </div>
      </main>
      <Footer dark />
    </div>
  );
}
