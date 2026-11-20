import { useState } from "react";
import { Card, ContentZone, PageHero, StatusBadge } from "../components/AppShell";
import { todaySessions, tomorrowSessions, type SessionStatus } from "../data";

type Filter = "today" | "tomorrow" | "all";

const statusTone: Record<SessionStatus, "success" | "warning" | "purple"> = {
  Selesai: "success",
  "Sedang berjalan": "warning",
  Berikutnya: "purple",
  Terjadwal: "purple",
};

export function SchedulePage() {
  const [filter, setFilter] = useState<Filter>("today");
  const sessions =
    filter === "today"
      ? todaySessions
      : filter === "tomorrow"
        ? tomorrowSessions
        : [...todaySessions, ...tomorrowSessions];
  const filters: { id: Filter; label: string }[] = [
    { id: "today", label: "Hari ini · 14 Nov" },
    { id: "tomorrow", label: "Besok · 15 Nov" },
    { id: "all", label: "Semua arena" },
  ];

  return (
    <>
      <PageHero
        title="Hari ini, giliran siapa?"
        subtitle="Sabtu, 14 November 2026 · Semua waktu dalam WIB"
        mascot="/assets/schedule/17d7a.png"
        schedule
      />
      <ContentZone ornament="/assets/schedule/98d5a.svg">
        <div className="chips">
          {filters.map((item) => (
            <button
              key={item.id}
              className={`filter-chip ${filter === item.id ? "selected" : ""}`}
              onClick={() => setFilter(item.id)}
            >
              {item.label}
            </button>
          ))}
        </div>
        <div className="stats-grid schedule-stats">
          {[
            [
              "SESI HARI INI",
              filter === "tomorrow" ? "02" : "24",
              filter === "tomorrow" ? "08.30–09.00 WIB" : "08.00–16.30 WIB",
            ],
            [
              "ARENA AKTIF",
              filter === "tomorrow" ? "02" : "03",
              filter === "tomorrow" ? "A · C" : "A · B · C",
            ],
            [
              "SESI BERIKUTNYA",
              filter === "tomorrow" ? "08:30" : "10:15",
              filter === "tomorrow" ? "AURORA · Arena A" : "NOVA · Arena A",
            ],
          ].map(([label, value, detail], index) => (
            <Card className="stat-card" delay={index * 70} key={`${filter}-${label}`}>
              <span className="antenna">
                <img src="/assets/shared/33664.svg" alt="" />
              </span>
              <span className="eyebrow neutral">{label}</span>
              <b className="stat-number">{value}</b>
              <p>{detail}</p>
            </Card>
          ))}
        </div>
        <Card delay={220}>
          <span className="eyebrow">JAM · TIM · ROBOT · STATUS</span>
          <h2>Jadwal arena</h2>
          <div className="data-list schedule-list">
            {sessions.map((session) => (
              <div
                className={`data-row ${session.isMine ? "my-team" : ""}`}
                key={`${session.day}-${session.time}-${session.team}`}
              >
                <div>
                  <strong>
                    {session.time} · {session.arena} · {session.team}{" "}
                    {session.isMine && <em>Tim saya</em>}
                  </strong>
                  <span>
                    {session.school} · {session.level} · {session.robot} · Sesi penyisihan
                  </span>
                </div>
                <StatusBadge tone={statusTone[session.status]}>{session.status}</StatusBadge>
              </div>
            ))}
          </div>
        </Card>
        <div className="notice warning">
          Jadwal dapat berubah jika ada gangguan arena. Pantau informasi dari operator sebelum
          check-in.
        </div>
      </ContentZone>
    </>
  );
}
