import { useEffect, useState } from "react";
import { FloatingRemote, Navbar, RemotePopup, type Vote } from "./components";
import type { Page } from "./data";
import { LeaderboardPage, ManualPage, RemotePage } from "./pages";

export default function App() {
  const [page, setPageState] = useState<Page>("remote");
  const [online, setOnline] = useState(true);
  const [remoteOpen, setRemoteOpen] = useState(true);
  const [pending, setPending] = useState(2);
  const [current, setCurrent] = useState<2 | 3>(2);
  const [lastVote, setLastVote] = useState<Vote | null>(null);
  const [review, setReview] = useState(false);
  const [toast, setToast] = useState("");
  const [offlineDraft, setOfflineDraft] = useState(false);
  const [synced, setSynced] = useState(false);

  const notify = (text: string) => setToast(text);
  useEffect(() => {
    if (!toast) return;
    const id = window.setTimeout(() => setToast(""), 2100);
    return () => window.clearTimeout(id);
  }, [toast]);

  const setPage = (next: Page) => {
    setPageState(next);
    setRemoteOpen(next === "remote");
  };
  const toggleOnline = () => {
    setOnline((value) => {
      if (!value && offlineDraft) {
        notify("Terkirim. Draft aman ✓");
        setOfflineDraft(false);
        setSynced(true);
      }
      return !value;
    });
  };
  const vote = (choice: Vote) => {
    if (!online) {
      setOfflineDraft(true);
      setSynced(false);
    }
    setLastVote(choice);
    notify(`✓ Pilihan kamu: ${choice}`);
    if (review) setReview(false);
    if (current === 2) {
      setCurrent(3);
      setPending(1);
    } else {
      setPending(0);
    }
  };
  const undo = () => {
    setCurrent(2);
    setPending(2);
    setLastVote(null);
  };
  return (
    <div className="app-shell">
      <Navbar
        page={page}
        setPage={setPage}
        online={online}
        toggleOnline={toggleOnline}
        remoteOpen={remoteOpen}
        setRemoteOpen={setRemoteOpen}
        pending={pending}
        notify={notify}
      />
      {page === "remote" && <RemotePage vote={lastVote} />}
      {page === "manual" && <ManualPage notify={notify} />}
      {page === "leaderboard" && <LeaderboardPage />}
      {!remoteOpen && <FloatingRemote pending={pending} open={() => setRemoteOpen(true)} />}
      <RemotePopup
        open={remoteOpen}
        onClose={() => setRemoteOpen(false)}
        online={online}
        pending={pending}
        current={current}
        lastVote={lastVote}
        review={review}
        synced={synced}
        onVote={vote}
        onUndo={undo}
        onReview={() => {
          setReview(true);
          setCurrent(2);
          setLastVote(null);
          setPending(2);
        }}
      />
      {toast && <div className="toast">{toast}</div>}
    </div>
  );
}
