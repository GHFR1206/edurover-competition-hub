import { useEffect, useRef, useState } from "react";
import {
  ArenaTabs,
  Footer,
  Hero,
  MascotMessage,
  Navbar,
  QueueCard,
  RunCard,
  SpecialHandling,
  type Filter,
  type IssueType,
} from "./components";
import { initialArenas, type ArenaId, type QueueItem, formatDuration } from "./data";
import { formatIndonesianDate, MIN_RERUN_DATE } from "./schedule-pickers";

export default function App() {
  const [arenas, setArenas] = useState(initialArenas);
  const [activeArena, setActiveArena] = useState<ArenaId>("A");
  const [filter, setFilter] = useState<Filter>("Semua");
  const [issue, setIssue] = useState<IssueType | null>(null);
  const [reason, setReason] = useState(
    "Sensor garis arena terputus saat CP2. Lintasan perlu dikalibrasi ulang.",
  );
  const [rerunDate, setRerunDate] = useState(MIN_RERUN_DATE);
  const [rerunTime, setRerunTime] = useState("11:15");
  const [replacement, setReplacement] = useState<ArenaId>("A");
  const [notice, setNotice] = useState("");
  const specialRef = useRef<HTMLElement>(null);
  const noticeTimer = useRef<number | null>(null);
  const arena = arenas[activeArena];

  const notify = (message: string) => {
    setNotice(message);
    if (noticeTimer.current) window.clearTimeout(noticeTimer.current);
    noticeTimer.current = window.setTimeout(() => setNotice(""), 2800);
  };

  useEffect(() => {
    const timer = window.setInterval(() => {
      setArenas((current) => {
        const next = { ...current };
        let changed = false;
        (Object.keys(next) as ArenaId[]).forEach((id) => {
          const run = next[id].run;
          if (run && run.elapsed < 300) {
            next[id] = { ...next[id], run: { ...run, elapsed: run.elapsed + 1 } };
            changed = true;
          }
        });
        return changed ? next : current;
      });
    }, 1000);
    return () => window.clearInterval(timer);
  }, []);

  const updateQueue = (item: QueueItem) => {
    const nextStatus =
      item.status === "Menunggu"
        ? "Dipanggil"
        : item.status === "Dipanggil"
          ? "Siap mulai"
          : "Berjalan";
    if (nextStatus === "Berjalan" && arena.run) {
      notify("Run ditolak: masih ada run berjalan di arena ini.");
      return;
    }
    setArenas((current) => ({
      ...current,
      [activeArena]: {
        ...current[activeArena],
        queue: current[activeArena].queue.map((row) =>
          row.id === item.id ? { ...row, status: nextStatus } : row,
        ),
        run:
          nextStatus === "Berjalan"
            ? {
                id: item.id,
                startTime: item.time,
                team: item.team,
                robot: item.robot,
                school: item.school,
                challenge: "Arena Mission Challenge",
                elapsed: 0,
              }
            : current[activeArena].run,
      },
    }));
    notify(
      nextStatus === "Dipanggil"
        ? `${item.team} berhasil dipanggil.`
        : nextStatus === "Siap mulai"
          ? `${item.team} siap memulai run.`
          : `Run ${item.team} dimulai.`,
    );
  };

  const finishRun = () => {
    if (!arena.run) return;
    const run = arena.run;
    const duration = formatDuration(run.elapsed);
    setArenas((current) => ({
      ...current,
      [activeArena]: {
        ...current[activeArena],
        run: null,
        queue: current[activeArena].queue.some((item) => item.id === run.id)
          ? current[activeArena].queue.map((item) =>
              item.id === run.id
                ? { ...item, status: "Selesai", detail: `Run selesai ${duration}`, duration }
                : item,
            )
          : [
              ...current[activeArena].queue,
              {
                id: `${run.id}-done`,
                time: run.startTime,
                team: run.team,
                robot: run.robot,
                school: run.school,
                detail: `Run selesai ${duration}`,
                status: "Selesai",
                duration,
              },
            ],
      },
    }));
    notify(`Run ${run.team} selesai dalam ${duration}.`);
  };

  const flagIssue = () => {
    setIssue("Gangguan arena");
    notify("Gangguan arena dipilih untuk penanganan khusus.");
    window.setTimeout(
      () => specialRef.current?.scrollIntoView({ behavior: "smooth", block: "center" }),
      50,
    );
  };

  const scheduleRerun = () => {
    if (!issue || !reason.trim()) {
      notify("Pilih status dan isi alasan sebelum menjadwalkan re-run.");
      return;
    }
    const [year, month, day] = rerunDate.split("-").map(Number);
    const [hour, minute] = rerunTime.split(":").map(Number);
    const selectedSchedule = new Date(year, month - 1, day, hour, minute);
    if (selectedSchedule.getTime() < Date.now()) {
      notify("Tanggal dan jam re-run sudah lewat. Pilih jadwal lain.");
      return;
    }
    const occupied = arenas[replacement].queue.some(
      (item) => (item.date ?? MIN_RERUN_DATE) === rerunDate && item.time === rerunTime,
    );
    if (occupied) {
      notify(`Jam ${rerunTime} di Arena ${replacement} sudah terisi. Pilih jam lain.`);
      return;
    }
    const source = arena.run ?? arena.queue[0];
    const newItem: QueueItem = {
      id: `rerun-${Date.now()}`,
      date: rerunDate,
      time: rerunTime,
      team: source?.team ?? "Tim re-run",
      robot: source?.robot ?? "RR-001",
      school: source?.school ?? "Peserta arena",
      detail: `Re-run ${formatIndonesianDate(rerunDate)} · ${issue} · dari Arena ${activeArena}`,
      status: "Menunggu",
    };
    setArenas((current) => ({
      ...current,
      [replacement]: { ...current[replacement], queue: [...current[replacement].queue, newItem] },
    }));
    notify(`Re-run dijadwalkan · ${formatIndonesianDate(rerunDate)}, ${rerunTime} WIB`);
  };

  return (
    <div className="app-shell">
      <Navbar />
      <Hero subtitle={arena.subtitle} />
      <main className="main-content">
        <div className="ornament gear">✿</div>
        <div className="ornament flag">▦</div>
        <div className="ornament hexagon" />
        <div className="ornament triangle" />
        <ArenaTabs
          active={activeArena}
          onChange={(id) => {
            setActiveArena(id);
            setReplacement(id);
            setFilter("Semua");
            notify(`Beralih ke Arena ${id}.`);
          }}
        />
        <RunCard arena={arena} onFinish={finishRun} onIssue={flagIssue} />
        <QueueCard
          items={arena.queue}
          filter={filter}
          onFilter={setFilter}
          onAdvance={updateQueue}
        />
        <SpecialHandling
          selected={issue}
          reason={reason}
          rerunDate={rerunDate}
          rerunTime={rerunTime}
          replacement={replacement}
          sectionRef={specialRef}
          onSelect={(value) => setIssue(value)}
          onReason={setReason}
          onRerunDate={setRerunDate}
          onRerunTime={setRerunTime}
          onReplacement={setReplacement}
          onSchedule={scheduleRerun}
        />
        <MascotMessage />
      </main>
      <Footer />
      {notice && (
        <div className="toast" role="status">
          {notice}
        </div>
      )}
    </div>
  );
}
