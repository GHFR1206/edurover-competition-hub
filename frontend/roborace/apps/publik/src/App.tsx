import { useEffect, useState } from "react";
import {
  ArchivePage,
  EventPage,
  HomePage,
  LeaderboardPage,
  ResultsPage,
  SchedulePage,
} from "./pages";
import type { Page } from "./data";

const validPages: Page[] = ["beranda", "event", "jadwal", "leaderboard", "hasil", "arsip"];

function readPage(): Page {
  const page = window.location.hash.replace("#/", "") as Page;
  return validPages.includes(page) ? page : "beranda";
}

export default function App() {
  const [page, setPage] = useState<Page>(readPage);
  const [expanded, setExpanded] = useState(false);

  useEffect(() => {
    const onHashChange = () => {
      setPage(readPage());
      setExpanded(false);
      window.scrollTo({ top: 0, behavior: "smooth" });
    };
    window.addEventListener("hashchange", onHashChange);
    return () => window.removeEventListener("hashchange", onHashChange);
  }, []);

  const go = (next: Page) => {
    if (next === page) window.scrollTo({ top: 0, behavior: "smooth" });
    window.location.hash = `/${next}`;
  };

  if (page === "event") return <EventPage page={page} go={go} />;
  if (page === "jadwal") return <SchedulePage page={page} go={go} />;
  if (page === "leaderboard")
    return (
      <LeaderboardPage
        page={page}
        go={go}
        onChampion={() => {
          setPage("hasil");
          setExpanded(true);
        }}
      />
    );
  if (page === "hasil")
    return (
      <ResultsPage
        page={page}
        go={go}
        expanded={expanded}
        onExpand={() => setExpanded((value) => !value)}
      />
    );
  if (page === "arsip") return <ArchivePage page={page} go={go} />;
  return (
    <HomePage
      page={page}
      go={go}
      onChampion={() => {
        setPage("hasil");
        setExpanded(true);
      }}
    />
  );
}
