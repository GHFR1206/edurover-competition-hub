import { useMemo, useState } from "react";
import { Badge, Button, Card } from "../components";
import { correctionTeams } from "../data";

type TeamKey = keyof typeof correctionTeams;
type History = {
  team: string;
  old: number;
  next: number;
  reason: string;
  time: string;
  field: string;
};

export function CorrectionPage({
  notify,
  log,
}: {
  notify: (text: string) => void;
  log: (text: string) => void;
}) {
  const [teamKey, setTeamKey] = useState<TeamKey>("VEGA");
  const [field, setField] = useState("2");
  const [value, setValue] = useState("10");
  const [reason, setReason] = useState(
    "Kesalahan input nilai juri ketiga: tertulis 9, seharusnya 10 sesuai lembar penilaian bertanda tangan.",
  );
  const [savedScores, setSavedScores] = useState<Record<TeamKey, number[]>>({
    VEGA: [...correctionTeams.VEGA.scores],
    TITAN: [...correctionTeams.TITAN.scores],
  });
  const [history, setHistory] = useState<History[]>([
    {
      team: "VEGA",
      old: 9,
      next: 10,
      reason: "Bukti lembar juri",
      time: "14 November 2026, 11:32 WIB",
      field: "Nilai manual · Juri ketiga",
    },
  ]);
  const team = correctionTeams[teamKey];
  const scores = savedScores[teamKey];
  const numeric = value === "" ? NaN : Number(value);
  const previewScores = scores.map((score, index) =>
    index === Number(field) && Number.isFinite(numeric) && numeric >= 0 && numeric <= 20
      ? numeric
      : score,
  );
  const average = previewScores.reduce((sum, score) => sum + score, 0) / previewScores.length;
  const total = team.robot + average;
  const oldTotal = team.robot + scores.reduce((sum, score) => sum + score, 0) / scores.length;
  const rank = useMemo(
    () => (teamKey === "VEGA" ? (total > 112 ? 1 : 2) : total < 108 ? 2 : 1),
    [teamKey, total],
  );
  const submit = () => {
    if (!reason.trim()) return notify("Alasan koreksi wajib diisi.");
    if (!Number.isFinite(numeric) || numeric < 0 || numeric > 20)
      return notify("Nilai baru harus berupa angka 0–20.");
    const old = scores[Number(field)];
    const nextScores = [...scores];
    nextScores[Number(field)] = numeric;
    setSavedScores({ ...savedScores, [teamKey]: nextScores });
    const entry = {
      team: teamKey,
      old,
      next: numeric,
      reason,
      time:
        new Date().toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" }) + " WIB",
      field: `Nilai manual · Juri ${["pertama", "kedua", "ketiga"][Number(field)]}`,
    };
    setHistory([entry, ...history]);
    notify(`Koreksi ${teamKey} tersimpan. Skor resmi ${format(total)} poin.`);
    log(
      `${entry.field} ${teamKey} dikoreksi ${old} → ${numeric} oleh Bima Pradana. Alasan: ${reason}`,
    );
  };
  const cancel = () => {
    setValue("");
    setReason("");
    notify("Form koreksi dikosongkan.");
    log(`Form koreksi ${teamKey} dibatalkan dan dikosongkan.`);
  };

  return (
    <>
      <div className="warning official-warning">
        Area hasil resmi. Koreksi akan memperbarui skor publik, peringkat, dan rekap. Pastikan bukti
        sudah diperiksa.
      </div>
      <div className="correction-grid">
        <Card>
          <h2>Buat koreksi resmi</h2>
          <label>Tim dan level</label>
          <select
            value={teamKey}
            onChange={(event) => {
              setTeamKey(event.target.value as TeamKey);
              setValue("");
            }}
          >
            {Object.entries(correctionTeams).map(([key, item]) => (
              <option key={key} value={key}>
                {key} · {item.id} · SLTP
              </option>
            ))}
          </select>
          <Badge tone="green">✓ Resmi · Skor total saat ini {format(oldTotal)}</Badge>
          <label>Penilaian yang dikoreksi</label>
          <select
            value={field}
            onChange={(event) => {
              setField(event.target.value);
              setValue(String(scores[Number(event.target.value)]));
            }}
          >
            <option value="0">Nilai manual · Juri pertama</option>
            <option value="1">Nilai manual · Juri kedua</option>
            <option value="2">Nilai manual · Juri ketiga</option>
          </select>
          <label>Nilai baru (0–20) *</label>
          <input
            type="number"
            min="0"
            max="20"
            value={value}
            onChange={(event) => setValue(event.target.value)}
          />
          <label>Alasan koreksi *</label>
          <textarea
            value={reason}
            onChange={(event) => setReason(event.target.value)}
            placeholder="Jelaskan alasan dan bukti koreksi..."
          />
          <small>* Alasan wajib. Nilai di luar 0–20 tidak dapat disimpan.</small>
          <div className="actions">
            <Button kind="yellow" onClick={submit}>
              Koreksi
            </Button>
            <Button onClick={cancel}>Batalkan</Button>
          </div>
        </Card>
        <Card>
          <h2>Pratinjau hasil</h2>
          <div className="data-row">
            <div>
              <strong>
                {teamKey} · {team.school}
              </strong>
              <span>
                {team.id} · Poin robot tetap {team.robot}
              </span>
            </div>
            <div className="score">{format(total)}</div>
            <Badge tone="green">✓ Resmi</Badge>
          </div>
          <div className="calculation">
            <p>Nilai manual: {previewScores.join(", ")}</p>
            <p>Rata-rata: {format(average)}</p>
            <p>
              Total setelah koreksi: {team.robot} + {format(average)} = {format(total)}
            </p>
          </div>
          <p className="muted">
            Sebelum koreksi: total {format(oldTotal)}. Peringkat{" "}
            {rank === team.rank ? "tetap" : "berubah menjadi"} #{rank}. Pembanding TITAN 112 poin.
          </p>
          <Badge>
            Peringkat #{rank} · {rank === team.rank ? "Tidak berubah" : `Sebelumnya #${team.rank}`}
          </Badge>
          <div className="mascot-note inset">
            <img src="/assets/17d7a.png" alt="Maskot RoboRace" />
            <strong>
              Koreksi tidak menghapus riwayat lama. Versi sebelumnya tetap dapat ditelusuri di audit
              log.
            </strong>
          </div>
        </Card>
      </div>
      <Card>
        <h2>Riwayat koreksi</h2>
        <div className="rows">
          {history.map((entry, index) => (
            <div className="data-row" key={`${entry.time}-${index}`}>
              <div>
                <strong>
                  {entry.team} · {entry.field} {entry.old} → {entry.next}
                </strong>
                <span>
                  Bima Pradana · {entry.time} · {entry.reason}
                </span>
              </div>
              <Badge tone="green">Koreksi resmi</Badge>
            </div>
          ))}
        </div>
      </Card>
    </>
  );
}

function format(value: number) {
  return Number.isInteger(value) ? String(value) : value.toFixed(2).replace(".", ",");
}
