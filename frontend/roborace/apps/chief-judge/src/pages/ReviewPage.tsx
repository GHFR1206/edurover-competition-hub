import { useMemo, useState } from "react";
import { Badge, Button, Card } from "../components";
import type { LevelName, Team } from "../data";

export function ReviewPage({
  teams,
  published,
  setPublished,
  navigate,
  notify,
  log,
}: {
  teams: Team[];
  published: Set<LevelName>;
  setPublished: (next: Set<LevelName>) => void;
  navigate: (page: "judging" | "correction" | "results") => void;
  notify: (text: string) => void;
  log: (text: string) => void;
}) {
  const [level, setLevel] = useState<LevelName>("SD");
  const levelTeams = useMemo(() => teams.filter((team) => team.level === level), [teams, level]);
  const allValid = levelTeams.every((team) => team.valid);
  const isPublished = published.has(level);
  const reviewCount = teams.reduce((total, team) => {
    if (team.valid) return total;
    return total + (team.name === "ORION" ? 2 : 1);
  }, 0);
  const reviewDetail =
    reviewCount === 3
      ? "2 pencapaian · 1 manual"
      : reviewCount === 2
        ? "2 pencapaian"
        : reviewCount === 1
          ? "1 nilai manual"
          : "Semua penilaian telah valid";
  const publish = () => {
    if (!allValid) return;
    const next = new Set(published).add(level);
    setPublished(next);
    notify(`Hasil ${level} berhasil disahkan dan dipublikasikan.`);
    log(`Publikasi ${level} disahkan oleh Bima Pradana.`);
  };

  return (
    <>
      <div className="stats">
        <Card>
          <span className="eyebrow">LEVEL SUDAH RESMI</span>
          <b>{String(published.size).padStart(2, "0")}</b>
          <p>
            {published.size
              ? [...published].join(", ") + " sudah dipublikasikan"
              : "Belum ada level resmi"}
          </p>
          <i>
            <img src="/assets/33664.svg" alt="" />
          </i>
        </Card>
        <Card>
          <span className="eyebrow">PERLU DITINJAU</span>
          <b>{String(reviewCount).padStart(2, "0")}</b>
          <p>{reviewDetail}</p>
          <i>
            <img src="/assets/33664.svg" alt="" />
          </i>
        </Card>
        <Card>
          <span className="eyebrow">TIM SELESAI</span>
          <b>{teams.filter((t) => t.valid).length}</b>
          <p>Dari {teams.length} tim contoh</p>
          <i>
            <img src="/assets/33664.svg" alt="" />
          </i>
        </Card>
      </div>
      <div className="chips">
        {(["SD", "SLTP", "SLTA"] as LevelName[]).map((item) => (
          <button
            key={item}
            className={level === item ? "selected" : ""}
            onClick={() => setLevel(item)}
          >
            {item}
          </button>
        ))}
      </div>
      <Card className="level-card">
        {!isPublished && !allValid && (
          <span className="eyebrow purple">BELUM SEMUA HASIL VALID</span>
        )}
        <h2>
          {level} · {isPublished ? "Hasil sudah resmi" : "Review sebelum publikasi"}
        </h2>
        <div className="rows">
          {levelTeams
            .sort((a, b) => b.score - a.score)
            .map((team) => (
              <div className="data-row" key={team.id}>
                <div>
                  <strong>
                    {team.name} · {team.id}
                  </strong>
                  <span>
                    {team.school} · {team.detail}
                  </span>
                </div>
                <div className="score">{team.score}</div>
                <Badge tone={isPublished ? "green" : team.valid ? "green" : "yellow"}>
                  {isPublished ? "✓ Resmi" : team.valid ? "Valid · Belum publikasi" : "◔ Sementara"}
                </Badge>
              </div>
            ))}
        </div>
        {isPublished ? (
          <>
            <p className="publish-meta">
              Disahkan Bima Pradana · 14 November 2026, 11:20 WIB. Jejak pengesahan tersimpan di
              audit log.
            </p>
            <div className="actions">
              <Button
                kind="primary"
                onClick={() => {
                  navigate("results");
                  log(`Hasil resmi ${level} dibuka oleh Bima Pradana.`);
                }}
              >
                Buka hasil resmi
              </Button>
              <Button onClick={() => navigate("correction")}>Koreksi resmi</Button>
            </div>
          </>
        ) : (
          <>
            {!allValid && (
              <div className="warning">
                Publikasi terkunci: selesaikan seluruh review penilaian.
              </div>
            )}
            <div className="actions">
              <Button kind="yellow" disabled={!allValid} onClick={publish}>
                Sahkan & publikasikan
              </Button>
              <Button onClick={() => navigate("judging")}>Tinjau penilaian</Button>
            </div>
          </>
        )}
      </Card>
      <div className="mascot-note">
        <img src="/assets/d8b40.png" alt="Maskot RoboRace" />
        <strong>
          Valid bukan berarti sudah Resmi. Tanda ✓ Resmi muncul hanya setelah level dipublikasikan.
        </strong>
      </div>
    </>
  );
}
