import { useState } from "react";
import { AppShell } from "./components/AppShell";
import { ResultsPage } from "./pages/ResultsPage";
import { SchedulePage } from "./pages/SchedulePage";
import { TeamPage } from "./pages/TeamPage";

export type Page = "team" | "results" | "schedule";

export default function App() {
  const [page, setPage] = useState<Page>("team");

  return (
    <AppShell page={page} onNavigate={setPage}>
      {page === "team" && <TeamPage onSeeResults={() => setPage("results")} />}
      {page === "results" && <ResultsPage />}
      {page === "schedule" && <SchedulePage />}
    </AppShell>
  );
}
