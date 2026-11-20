import { useState } from "react";
import { Footer } from "../components";

const checkpoints = [
  ["S", "Start", "start"],
  ["1", "CP1", "checkpoint"],
  ["2", "CP2", "checkpoint"],
  ["3", "CP3", "checkpoint"],
  ["FIN", "Finish", "finish"],
] as const;

const params = [
  ["Task Completion", "50"],
  ["Navigation / Accuracy", "25"],
  ["Efficiency / Time", "15"],
  ["Autonomy / Innovation", "10"],
] as const;

export function OfficialResultsPage({ notify }: { notify: (text: string) => void }) {
  const [expanded, setExpanded] = useState(false);
  const celebrationAssets = expanded
    ? {
        trophyLeft: "/assets/2342f.svg",
        trophyRight: "/assets/facc9.svg",
        trophyCup: "/assets/84f87.svg",
      }
    : {
        trophyLeft: "/assets/5452d.svg",
        trophyRight: "/assets/2737c.svg",
        trophyCup: "/assets/d1eca.svg",
      };

  return (
    <>
      <div className="official-results">
        <img className="results-sunburst" src="/assets/4a0a9.svg" alt="" />
        <Celebration expanded={expanded} />
        <section className="results-hero">
          <div className="results-status">
            <span>● LANGSUNG · 14 NOV 2026</span>
            <span>Seri Pilot</span>
          </div>
          <h1>Hasil Resmi</h1>
          <p>Smart Warehouse Challenge · Batas waktu 6 menit</p>
        </section>

        <section className="podium-section">
          <div className="results-mascot-stage">
            <img src="/assets/786e4.png" alt="Maskot RoboRace merayakan hasil resmi" />
          </div>
          <div className="podium">
            <div className="podium-stand second">
              <b>2</b>
              <strong>VEGA</strong>
              <span>108 poin</span>
              <small>Waktu: —</small>
            </div>
            <div className="podium-stand first">
              <em>
                <img src={celebrationAssets.trophyLeft} alt="" /> JUARA 1
              </em>
              <b>1</b>
              <strong>TITAN</strong>
              <span>112 poin</span>
              <small>SMP Garuda A · NR-114 · 02:14</small>
              <span className="podium-trophy" role="img" aria-label="Piala juara pertama">
                <img className="trophy-handle left" src={celebrationAssets.trophyLeft} alt="" />
                <img className="trophy-cup" src={celebrationAssets.trophyCup} alt="" />
                <img className="trophy-handle right" src={celebrationAssets.trophyRight} alt="" />
              </span>
            </div>
            <div className="podium-stand third">
              <b>3</b>
              <strong>Belum ditetapkan</strong>
              <small>◔ Menunggu hasil valid</small>
            </div>
          </div>
          <button
            className="results-download"
            onClick={() => notify("Rekap resmi sedang disiapkan.")}
          >
            Unduh rekap resmi
          </button>
        </section>

        <div className="results-dashed" />

        <section className="results-section">
          <div className="results-filters">
            <button>SD</button>
            <span>Sekolah Dasar</span>
            <button className="active">SLTP</button>
            <span className="active-label">Sekolah Lanjutan Tingkat Pertama</span>
            <button>SLTA</button>
            <span>Sekolah Lanjutan Tingkat Atas</span>
            <i />
            <strong>✓ SLTP · DIPUBLIKASIKAN</strong>
          </div>

          <div className="official-table">
            <div className="official-table-head">
              <span>Rank</span>
              <span>Tim · Sekolah</span>
              <span>Total skor</span>
              <span>Waktu</span>
              <span />
            </div>
            <div className={`official-team titan ${expanded ? "expanded" : ""}`}>
              <button
                className="official-team-row"
                onClick={() => setExpanded(!expanded)}
                aria-expanded={expanded}
              >
                <Rank value="1" tone="gold" />
                <span className="official-team-info">
                  <span className="name-row">
                    <b>TITAN</b>
                    <em>
                      <img src={celebrationAssets.trophyLeft} alt="" /> JUARA
                    </em>
                  </span>
                  <small>SMP Garuda A · NR-114</small>
                </span>
                <b className="official-score">112</b>
                <strong className="official-time">02:14</strong>
                <img
                  className={expanded ? "chevron up" : "chevron"}
                  src="/assets/2218e.svg"
                  alt={expanded ? "Tutup rincian" : "Buka rincian"}
                />
              </button>
              {expanded && <TitanDetails />}
            </div>
            <button className="official-team-row">
              <Rank value="2" tone="silver" />
              <span className="official-team-info">
                <b>VEGA</b>
                <small>SMP —</small>
              </span>
              <b className="official-score">108</b>
              <strong className="official-time">—</strong>
              <img className="chevron" src="/assets/2218e.svg" alt="Buka rincian VEGA" />
            </button>
            <div className="official-team-row pending">
              <Rank value="3" tone="bronze" />
              <span className="official-team-info">
                <b>Belum ditetapkan</b>
                <small>Menunggu hasil valid</small>
              </span>
              <b className="official-score">—</b>
              <strong className="official-time">—</strong>
              <img className="chevron" src="/assets/2218e.svg" alt="" />
            </div>
            <div className="official-table-foot">
              Nilai juri 0–20 bersifat aditif terhadap skor robot. Ketuk baris untuk rincian
              checkpoint.
            </div>
          </div>
          <div className="results-notice">
            <b>◔ SLTA</b>
            <span>Hasil belum dipublikasikan — juri masih menuntaskan penilaian.</span>
          </div>
        </section>
      </div>
      <Footer waveAsset={expanded ? "/assets/b6196.svg" : "/assets/c9747.svg"} />
    </>
  );
}

function Rank({ value, tone }: { value: string; tone: string }) {
  return <span className={`official-rank ${tone}`}>{value}</span>;
}

function TitanDetails() {
  return (
    <div className="titan-details">
      <h2>Score Breakdown · TITAN</h2>
      <div className="titan-summary">
        <span>
          <img src="/assets/786e4.png" alt="" />
        </span>
        <div>
          <b>TITAN</b>
          <small>SMP Garuda A · NR-114</small>
        </div>
        <strong>112</strong>
      </div>
      <h3>Parameter SLTP</h3>
      <div className="reference-notice">
        <b>Referensi konfigurasi · nilai parameter belum tersedia</b>
        <span>
          Total 112 tersedia, namun belum dipetakan ke parameter. Nilai di bawah menunjukkan
          referensi konfigurasi per jenjang.
        </span>
      </div>
      <div className="parameter-card open">
        <div className="parameter-head">
          <span>
            <b>Task Completion</b>
            <small>Poin — · Referensi 50</small>
          </span>
          <img src="/assets/4f44e.svg" alt="" />
        </div>
        <div className="checkpoint-list">
          {checkpoints.map(([mark, label, tone]) => (
            <div className="checkpoint-row" key={label}>
              <span>
                <i className={tone}>{mark}</i>
                <b>{label}</b>
              </span>
              <span>
                <small>Status —</small>
                <small>Poin —</small>
                <small className={label === "Finish" ? "finish-time" : ""}>
                  {label === "Finish" ? "Waktu: 02:14" : "Waktu —"}
                </small>
              </span>
            </div>
          ))}
          <small>Catatan: belum tersedia</small>
        </div>
      </div>
      <div className="parameter-card open">
        <div className="parameter-head">
          <span>
            <b>Navigation / Accuracy</b>
            <small>Poin — · Referensi 25</small>
          </span>
          <img src="/assets/4f44e.svg" alt="" />
        </div>
        <div className="parameter-mini">
          <span>Nilai juri —</span>
          <span>Referensi 25</span>
          <small>Catatan: belum tersedia</small>
        </div>
      </div>
      {params.slice(2).map(([name, reference]) => (
        <div className="parameter-card" key={name}>
          <div className="parameter-head">
            <span>
              <b>{name}</b>
              <small>Poin — · Referensi {reference}</small>
            </span>
            <img src="/assets/2218e.svg" alt="" />
          </div>
        </div>
      ))}
      <BonusPenalty />
      <div className="official-calculation">
        <b>Perhitungan</b>
        <span>
          <strong>Subtotal parameter</strong>
          <small>—</small>
        </span>
        <span className="bonus">
          <strong>Bonus total</strong>
          <small>—</small>
        </span>
        <span className="penalty">
          <strong>Penalti total</strong>
          <small>—</small>
        </span>
        <span>
          <strong>Skor resmi terbit</strong>
          <em>112</em>
        </span>
      </div>
    </div>
  );
}

function BonusPenalty() {
  return (
    <div className="bonus-penalty">
      <h3>Bonus &amp; Penalti</h3>
      <div>
        <span>
          <i>+</i>
          <b>+ Bonus</b>
        </span>
        <strong>—</strong>
      </div>
      <div>
        <span>
          <i>−</i>
          <b>− Penalti</b>
        </span>
        <strong>—</strong>
      </div>
      <div>
        <b>Net penyesuaian</b>
        <strong>—</strong>
      </div>
      <div className="adjustment-notice">
        <b>Rincian penyesuaian belum tersedia</b>
        <span>
          — bukan nol; belum dapat dipastikan apakah penyesuaian sudah termasuk dalam rekap.
        </span>
      </div>
      <div className="adjustment-table">
        <div>
          <b>Jenis</b>
          <b>Alasan</b>
          <b>Poin</b>
          <b>Waktu</b>
          <b>Dicatat oleh</b>
        </div>
        <p>Belum tersedia</p>
      </div>
    </div>
  );
}

function Celebration({ expanded }: { expanded: boolean }) {
  const assets = expanded
    ? ["/assets/090e7.svg", "/assets/20c45.svg", "/assets/24556.svg", "/assets/3970f.svg"]
    : ["/assets/09b4e.svg", "/assets/97ccf.svg", "/assets/23171.svg", "/assets/40153.svg"];
  const confetti = [
    ["purple", "8%", "10%"],
    ["yellow", "18%", "35%"],
    ["pink", "30%", "14%"],
    ["green", "40%", "30%"],
    ["purple", "55%", "18%"],
    ["yellow", "68%", "34%"],
    ["pink", "78%", "15%"],
    ["green", "88%", "27%"],
    ["yellow", "94%", "12%"],
    ["purple", "14%", "48%"],
    ["pink", "72%", "47%"],
    ["green", "32%", "43%"],
  ];
  return (
    <div className="celebration" aria-hidden="true">
      {confetti.map(([tone, left, top], index) => (
        <i
          key={`${left}-${top}`}
          className={tone}
          style={{ left, top, animationDelay: `${index * -0.12}s` }}
        />
      ))}
      <img className="result-star star-left" src={assets[0]} alt="" />
      <img className="result-star star-right" src={assets[1]} alt="" />
      <img className="result-star shape-left" src={assets[2]} alt="" />
      <img className="result-star shape-right" src={assets[3]} alt="" />
    </div>
  );
}
