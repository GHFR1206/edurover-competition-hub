import { useEffect, useState } from "react";
import { PageShell, Toast, type Page } from "./components";
import { initialTeams, type LevelName, type Team } from "./data";
import { CorrectionPage } from "./pages/CorrectionPage";
import { JudgingPage } from "./pages/JudgingPage";
import { LeaderboardPage } from "./pages/LeaderboardPage";
import { OfficialResultsPage } from "./pages/OfficialResultsPage";
import { ReviewPage } from "./pages/ReviewPage";

export default function App() {
  const [page, setPage] = useState<Page>("review");
  const [teams, setTeams] = useState<Team[]>(initialTeams);
  const [published, setPublished] = useState<Set<LevelName>>(new Set(["SLTP"]));
  const [toast, setToast] = useState("");
  const [audit, setAudit] = useState<string[]>([
    "SLTP dipublikasikan oleh Bima Pradana · 14 November 2026, 11:20 WIB",
  ]);
  const navigate = (next: Page) => {
    setPage(next);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };
  const notify = (message: string) => setToast(message);
  const log = (entry: string) =>
    setAudit((items) => [
      `${new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })} · ${entry}`,
      ...items,
    ]);
  useEffect(() => {
    if (!toast) return;
    const id = window.setTimeout(() => setToast(""), 3200);
    return () => window.clearTimeout(id);
  }, [toast]);

  return (
    <>
      <PageShell page={page} navigate={navigate}>
        {page === "review" && (
          <ReviewPage
            teams={teams}
            published={published}
            setPublished={setPublished}
            navigate={navigate}
            notify={notify}
            log={log}
          />
        )}
        {page === "judging" && (
          <JudgingPage teams={teams} setTeams={setTeams} notify={notify} log={log} />
        )}
        {page === "correction" && <CorrectionPage notify={notify} log={log} />}
        {page === "results" && <OfficialResultsPage notify={notify} />}
        {page === "leaderboard" && <LeaderboardPage teams={teams} published={published} />}
        {page !== "results" && (
          <details className="audit">
            <summary>Audit log sesi · {audit.length} aktivitas</summary>
            {audit.map((entry, index) => (
              <p key={`${entry}-${index}`}>{entry}</p>
            ))}
          </details>
        )}
      </PageShell>
      <Toast message={toast} />
    </>
  );
}
