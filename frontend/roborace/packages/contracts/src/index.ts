// Kontrak data RoboRace: satu sumber kebenaran untuk frontend dan backend.
// Status berupa ENUM (bukan teks tampilan). Teks seperti "3 setuju · 1 tolak" disusun frontend dari angka.
export type Level = "SD" | "SLTP" | "SLTA";
export type Role = "super_admin" | "event_admin" | "operator_arena" | "juri" | "chief_judge" | "coach";
export type EventStatus = "draft" | "open_regis" | "ongoing" | "finished"; // satu arah, tidak bisa mundur
export type Verification = "draft" | "pending" | "verified" | "rejected" | "needs_revision";
export type SessionStatus = "scheduled" | "next" | "called" | "ready" | "running" | "done" | "no_show" | "dnf" | "dq" | "disrupted";
export type AchievementCode = "START" | "CP1" | "CP2" | "CP3" | "FINISH";
export const ACHIEVEMENT_POINTS: Record<AchievementCode, number> = { START: 0, CP1: 20, CP2: 20, CP3: 20, FINISH: 40 }; // total 100
export type VoteChoice = "approve" | "reject";
export type Decision = "pending" | "approved" | "rejected" | "re_review"; // keputusan akhir hanya oleh Chief Judge
export type ResultStatus = "provisional" | "valid" | "official"; // official hanya setelah level dipublikasikan

export interface User { id: string; name: string; email: string; role: Role; active: boolean }
export interface EventEntity { id: string; name: string; status: EventStatus; startDate: string; endDate: string; location: string; levels: Level[] }
export interface Team { id: string; eventId: string; name: string; institution: string; level: Level; verification: Verification; reviewerNote?: string; coachId: string }
export interface Member { id: string; teamId: string; name: string; classOrRole: string; complete: boolean }
export interface Robot { teamId: string; robotCode: string; apiKeyHash: string } // API key asli hanya tampil sekali
export interface Session { id: string; teamId: string; arena: string; level: Level; startsAt: string; slotMinutes: number; status: SessionStatus; note?: string } // unik per (arena, startsAt) dan (teamId, startsAt)
export interface Achievement { id: string; sessionId: string; code: AchievementCode; receivedAt: string; source: "telemetry" | "manual"; decision: Decision; decidedBy?: string; reason?: string }
export interface Vote { achievementId: string; judgeId: string; choice: VoteChoice; at: string } // boleh diubah selama decision belum final
export interface ManualScore { sessionId: string; judgeId: string; value: number /* 0–20 */; note?: string; evidenceUrl?: string; submittedAt?: string }
export interface Correction { id: string; manualScoreId: string; oldValue: number; newValue: number; reason: string; by: string; at: string } // riwayat tidak pernah dihapus
export interface Publication { eventId: string; level: Level; publishedBy: string; at: string }
export interface AuditEntry { id: string; actorId: string; action: string; object: string; before?: unknown; after?: unknown; at: string }
// Aturan hitung (server wajib jadi sumber kebenaran): total = Σ poin pencapaian berstatus approved + rata-rata nilai manual juri
// Kuorum = floor(jumlahJuriPanel / 2) + 1. Mayoritas hanya menandai "menunggu Chief"; poin resmi hanya setelah Chief menyetujui.
export const quorum = (panelSize: number) => Math.floor(panelSize / 2) + 1;
export const totalScore = (approved: AchievementCode[], manual: number[]) =>
  approved.reduce((s, c) => s + ACHIEVEMENT_POINTS[c], 0) + (manual.length ? manual.reduce((a, b) => a + b, 0) / manual.length : 0);
