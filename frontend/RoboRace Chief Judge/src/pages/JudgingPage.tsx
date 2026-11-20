import { useMemo, useState } from "react";
import { Badge, Button, Card } from "../components";
import { reviewItems, type ReviewItem, type Team } from "../data";

type Decision = { item: ReviewItem; action: string; reason: string; time: string };
type Tab = "pending" | "achievement" | "manual" | "done";

export function JudgingPage({
  teams,
  setTeams,
  notify,
  log,
}: {
  teams: Team[];
  setTeams: (teams: Team[]) => void;
  notify: (text: string) => void;
  log: (text: string) => void;
}) {
  const [tab, setTab] = useState<Tab>("pending");
  const [evidencePreview, setEvidencePreview] = useState("");
  const [reasons, setReasons] = useState<Record<string, string>>(() =>
    Object.fromEntries(reviewItems.map((item) => [item.id, item.defaultReason])),
  );
  const [decisions, setDecisions] = useState<Decision[]>([
    {
      item: { ...reviewItems[0], id: "cp1", name: "CP1" },
      action: "disetujui",
      reason: "Semua bukti valid",
      time: "10:01:22 WIB",
    },
    {
      item: { ...reviewItems[0], id: "cp3", name: "CP3" },
      action: "ditinjau ulang",
      reason: "Rekaman tertutup petugas",
      time: "10:03:01 WIB",
    },
  ]);
  const completed = new Set(decisions.map((decision) => decision.item.id));
  const pending = reviewItems.filter((item) => !completed.has(item.id));
  const visible = useMemo(() => {
    if (tab === "done") return [];
    if (tab === "achievement") return pending.filter((item) => item.type === "achievement");
    if (tab === "manual") return pending.filter((item) => item.type === "manual");
    return pending;
  }, [tab, decisions]);

  const decide = (item: ReviewItem, action: "disetujui" | "ditolak" | "ditinjau ulang") => {
    const reason = reasons[item.id]?.trim() || "";
    if (action !== "disetujui" && !reason) {
      notify(`Alasan wajib diisi untuk keputusan ${action}.`);
      return;
    }
    const decision = {
      item,
      action,
      reason: reason || "Disetujui tanpa catatan tambahan",
      time: new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }) + " WIB",
    };
    const next = [decision, ...decisions];
    setDecisions(next);
    const remainingForTeam = reviewItems.filter(
      (review) =>
        review.team === item.team &&
        review.id !== item.id &&
        !next.some((done) => done.item.id === review.id),
    );
    if (!remainingForTeam.length)
      setTeams(
        teams.map((team) =>
          team.name === item.team
            ? { ...team, valid: true, detail: "Seluruh pencapaian dan nilai telah valid" }
            : team,
        ),
      );
    const result =
      action === "disetujui" && item.points
        ? `${item.points} poin masuk ke total sementara ${item.team}.`
        : `Status ${item.team} diperbarui.`;
    notify(`Keputusan ${item.name} disimpan. ${result}`);
    log(`${item.team} ${item.name} ${action} oleh Bima Pradana. Alasan: ${decision.reason}`);
  };

  return (
    <>
      <div className="chips tabs">
        {[
          ["pending", `Perlu keputusan · ${pending.length}`],
          ["achievement", "Pencapaian"],
          ["manual", "Nilai manual"],
          ["done", "Selesai ditinjau"],
        ].map(([id, label]) => (
          <button
            key={id}
            className={tab === id ? "selected" : ""}
            onClick={() => setTab(id as Tab)}
          >
            {label}
          </button>
        ))}
      </div>
      {visible.map((item) => (
        <DecisionCard
          key={item.id}
          item={item}
          reason={reasons[item.id] || ""}
          setReason={(value) => setReasons({ ...reasons, [item.id]: value })}
          decide={decide}
          showEvidence={(name) => {
            setEvidencePreview(name);
            notify(`Pratinjau bukti ${name} dibuka.`);
            log(`Bukti arena ${name} dibuka oleh Bima Pradana.`);
          }}
        />
      ))}
      {(tab === "done" || tab === "pending") && (
        <Card>
          <h2>Keputusan terbaru</h2>
          <div className="rows">
            {decisions.map((decision) => (
              <div className="data-row" key={`${decision.item.id}-${decision.time}`}>
                <div>
                  <strong>
                    {decision.item.team} · {decision.item.name} {decision.action}
                  </strong>
                  <span>
                    Bima Pradana · {decision.time} · {decision.reason}
                  </span>
                </div>
                <Badge
                  tone={
                    decision.action === "disetujui"
                      ? "green"
                      : decision.action === "ditolak"
                        ? "red"
                        : "yellow"
                  }
                >
                  {decision.action === "disetujui"
                    ? "Valid"
                    : decision.action === "ditolak"
                      ? "Ditolak"
                      : "Perlu bukti tambahan"}
                </Badge>
              </div>
            ))}
          </div>
        </Card>
      )}
      {!visible.length && tab !== "done" && (
        <div className="success-banner">Semua item pada tab ini telah selesai ditinjau.</div>
      )}
      {evidencePreview && (
        <div
          className="evidence-modal"
          role="dialog"
          aria-modal="true"
          aria-label="Pratinjau bukti arena"
        >
          <Card className="evidence-card">
            <span className="eyebrow purple">PRATINJAU BUKTI ARENA</span>
            <h2>{evidencePreview}</h2>
            <div className="arena-preview">
              <span>CP2</span>
              <i />
              <strong>Lintasan Arena A · cuplikan 00:18</strong>
            </div>
            <p className="muted">
              Rekaman dummy memperlihatkan seluruh roda robot melewati garis checkpoint.
            </p>
            <Button kind="primary" onClick={() => setEvidencePreview("")}>
              Tutup pratinjau
            </Button>
          </Card>
        </div>
      )}
    </>
  );
}

function DecisionCard({
  item,
  reason,
  setReason,
  decide,
  showEvidence,
}: {
  item: ReviewItem;
  reason: string;
  setReason: (value: string) => void;
  decide: (item: ReviewItem, action: "disetujui" | "ditolak" | "ditinjau ulang") => void;
  showEvidence: (name: string) => void;
}) {
  return (
    <Card className="decision-card">
      <span className="eyebrow purple">{item.eyebrow}</span>
      <h2>{item.title}</h2>
      <div className="data-row">
        <div>
          <strong>
            {item.type === "achievement"
              ? `${item.name} diterima · Arena A`
              : "Akurasi penempatan paket"}
          </strong>
          <span>{item.meta}</span>
        </div>
        {item.type === "manual" && <div className="score">{item.points}</div>}
        <Badge tone="yellow">{item.badge}</Badge>
      </div>
      {item.description && <p className="decision-description">{item.description}</p>}
      {item.evidence && (
        <div className="actions">
          <Button onClick={() => showEvidence(item.evidence || "")}>Lihat bukti arena</Button>
          <Badge>{item.evidence}</Badge>
        </div>
      )}
      <label>Alasan {item.type === "achievement" ? "keputusan" : "tinjauan"} *</label>
      <textarea
        value={reason}
        onChange={(event) => setReason(event.target.value)}
        placeholder="Tuliskan alasan keputusan Chief..."
      />
      <small>* Wajib diisi untuk Tolak atau Tinjau ulang. Alasan disimpan bersama keputusan.</small>
      <div className="actions">
        <Button kind="primary" onClick={() => decide(item, "disetujui")}>
          Setujui
        </Button>
        <Button kind="danger" onClick={() => decide(item, "ditolak")}>
          Tolak
        </Button>
        <Button onClick={() => decide(item, "ditinjau ulang")}>Tinjau ulang</Button>
      </div>
    </Card>
  );
}
