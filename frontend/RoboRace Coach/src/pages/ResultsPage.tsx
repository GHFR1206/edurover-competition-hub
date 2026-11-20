import { useRef, useState } from "react";
import { Card, ContentZone, PageHero, StatusBadge, Toast } from "../components/AppShell";
import { MascotMessage } from "./TeamPage";

const stats = [
  ["SKOR RESMI TITAN", "112", "100 robot + 12 manual"],
  ["PERINGKAT SLTP", "#1", "Waktu terbaik 02:14"],
  ["PENCAPAIAN", "5/5", "START sampai FINISH disahkan"],
];

export function ResultsPage() {
  const [toast, setToast] = useState("");
  const timer = useRef<number | undefined>(undefined);
  const notify = (message: string) => {
    setToast(message);
    window.clearTimeout(timer.current);
    timer.current = window.setTimeout(() => setToast(""), 2200);
  };

  return (
    <>
      <PageHero
        title="Kerja kerasmu, tercatat."
        subtitle="Rekap hasil dan sertifikat tim sekolahmu."
        mascot="/assets/results/786e4.png"
      />
      <ContentZone ornament="/assets/results/98d5a.svg">
        <div className="stats-grid">
          {stats.map(([label, value, detail], index) => (
            <Card className="stat-card" delay={index * 70} key={label}>
              <span className="antenna">
                <img src="/assets/shared/33664.svg" alt="" />
              </span>
              <span className="eyebrow neutral">{label}</span>
              <b className="stat-number" style={{ animationDelay: `${180 + index * 70}ms` }}>
                {value}
              </b>
              <p>{detail}</p>
            </Card>
          ))}
        </div>
        <Card delay={220}>
          <span className="eyebrow">NR-114 · ROBO RACE CUP 2026</span>
          <h2>TITAN · SMP Garuda A</h2>
          <StatusBadge>✓ Resmi · Level SLTP dipublikasikan</StatusBadge>
          <div className="score-row">
            <div>
              <strong>Run penyisihan · Arena B</strong>
              <span>14 November 2026 · 09:30 WIB · Durasi 02:14</span>
            </div>
            <div>
              <b>112</b>
              <StatusBadge>✓ Resmi</StatusBadge>
            </div>
          </div>
          <p className="body-dark">
            START 0 · CP1 20 · CP2 20 · CP3 20 · FINISH 40
            <br />
            Nilai manual: 10, 12, 14 → rata-rata 12
          </p>
          <div className="actions">
            <button
              className="button yellow"
              onClick={() => notify("Sertifikat TITAN siap diunduh")}
            >
              Unduh sertifikat
            </button>
            <button
              className="button secondary"
              onClick={() => notify("Rekap skor TITAN siap diunduh")}
            >
              Unduh rekap skor
            </button>
          </div>
        </Card>
        <Card delay={290}>
          <span className="eyebrow">NR-014 · LEVEL SD</span>
          <h2>ORION · SD Nusantara 1</h2>
          <StatusBadge tone="warning">◔ Sementara</StatusBadge>
          <p className="body-dark">
            Hasil belum disahkan oleh Chief Judge. Sertifikat tersedia setelah seluruh level SD
            dipublikasikan.
          </p>
          <div className="actions">
            <button className="button secondary" disabled>
              Unduh sertifikat
            </button>
            <button className="button secondary" disabled>
              Unduh rekap skor
            </button>
          </div>
        </Card>
        <MascotMessage image="/assets/results/e638f.png">
          Bukan hanya medali. Setiap eksperimen, perbaikan, dan kerja sama tim adalah pencapaianmu.
        </MascotMessage>
      </ContentZone>
      <Toast message={toast} />
    </>
  );
}
