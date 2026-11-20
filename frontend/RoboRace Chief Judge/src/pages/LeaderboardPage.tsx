import { useState } from "react";
import { Badge, Card } from "../components";
import type { LevelName, Team } from "../data";

export function LeaderboardPage({
  teams,
  published,
}: {
  teams: Team[];
  published: Set<LevelName>;
}) {
  const [level, setLevel] = useState<LevelName>("SD");
  const rows = teams.filter((team) => team.level === level).sort((a, b) => b.score - a.score);
  return (
    <>
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
      <Card className="leaderboard">
        <span className="eyebrow purple">PERINGKAT {level}</span>
        <h2>Leaderboard · {published.has(level) ? "Hasil resmi" : "Hasil sementara"}</h2>
        <div className="rows">
          {rows.map((team, index) => (
            <div className="rank-row" key={team.id}>
              <div className={`rank rank-${index + 1}`}>{String(index + 1).padStart(2, "0")}</div>
              <div>
                <strong>
                  {team.name} · {team.id}
                </strong>
                <span>
                  {team.school} · {team.detail}
                </span>
              </div>
              <div className="score">{team.score}</div>
              <Badge tone={published.has(level) ? "green" : "yellow"}>
                {published.has(level) ? "✓ Resmi" : "◔ Sementara"}
              </Badge>
            </div>
          ))}
        </div>
      </Card>
    </>
  );
}
