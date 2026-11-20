import { useRef, useState } from "react";
import { achievementSteps, initialMembers } from "../data";
import { Card, ContentZone, PageHero, StatusBadge, Toast } from "../components/AppShell";

export function TeamPage({ onSeeResults }: { onSeeResults: () => void }) {
  const [level, setLevel] = useState("SLTP");
  const [teamName, setTeamName] = useState("");
  const [memberName, setMemberName] = useState("");
  const [memberRole, setMemberRole] = useState("");
  const [members, setMembers] = useState(initialMembers);
  const [toast, setToast] = useState("");
  const timer = useRef<number | undefined>(undefined);

  const notify = (message: string) => {
    setToast(message);
    window.clearTimeout(timer.current);
    timer.current = window.setTimeout(() => setToast(""), 2200);
  };

  const createTeam = () => {
    if (!teamName.trim()) return notify("Nama tim tidak boleh kosong");
    notify(`Tim ${teamName.trim()} siap dibuat`);
  };

  const addMember = () => {
    const name = memberName.trim() || "Anggota baru";
    const detail = memberRole.trim() || "Anggota tim · Data akan dilengkapi";
    setMembers((current) => [...current, { name, detail }]);
    setMemberName("");
    setMemberRole("");
    notify("Anggota berhasil ditambahkan");
  };

  const copyApiKey = async () => {
    try {
      await navigator.clipboard.writeText("rr_pilot_demo_key_7A2F");
    } catch {
      /* Clipboard may be restricted in preview. */
    }
    notify("API key disalin");
  };

  return (
    <>
      <PageHero
        title="Tim kecil, mimpi besar."
        subtitle="Halo, Rani! Siapkan timmu untuk petualangan berikutnya."
        mascot="/assets/team/e638f.png"
      />
      <ContentZone ornament="/assets/team/98d5a.svg">
        <div className="two-column">
          <Card delay={40}>
            <Eyebrow>SMP GARUDA A · SLTP</Eyebrow>
            <h2>TITAN</h2>
            <StatusBadge>✓ Terverifikasi</StatusBadge>
            <p className="body-dark">RoboRace Cup 2026 (Seri Pilot) · NR-114</p>
            <div className="notice success">
              Catatan reviewer: Dokumen sekolah dan data anggota sudah sesuai. Selamat berlomba!
            </div>
            <div className="actions">
              <button className="button secondary">Edit tim</button>
              <button className="button primary" onClick={onSeeResults}>
                Lihat hasil
              </button>
            </div>
          </Card>
          <Card delay={100}>
            <Eyebrow>EVENT BERIKUTNYA</Eyebrow>
            <h2>Buat tim</h2>
            <p>Belum punya tim untuk Regional Seri 2? Mulai dari nama yang hebat.</p>
            <Field label="Nama tim">
              <input
                value={teamName}
                onChange={(event) => setTeamName(event.target.value)}
                placeholder="Nama tim baru"
              />
            </Field>
            <Field label="Institusi">
              <input defaultValue="SMP Garuda A" />
            </Field>
            <Field label="Event">
              <select defaultValue="regional">
                <option value="regional">RoboRace Regional Seri 2</option>
              </select>
            </Field>
            <div className="chips" aria-label="Pilih jenjang">
              {["SD", "SLTP", "SLTA"].map((item) => (
                <button
                  key={item}
                  className={`filter-chip ${level === item ? "selected" : ""}`}
                  onClick={() => setLevel(item)}
                >
                  {item}
                </button>
              ))}
            </div>
            <button className="button yellow" onClick={createTeam}>
              Buat tim
            </button>
          </Card>
        </div>

        <Card delay={160}>
          <h2>Anggota tim</h2>
          <div className="data-list">
            {members.map((member, index) => (
              <div className="data-row" key={`${member.name}-${index}`}>
                <div>
                  <strong>{member.name}</strong>
                  <span>{member.detail}</span>
                </div>
                <StatusBadge>Lengkap</StatusBadge>
              </div>
            ))}
          </div>
          <div className="form-grid">
            <Field label="Nama anggota baru">
              <input
                value={memberName}
                onChange={(event) => setMemberName(event.target.value)}
                placeholder="Nama lengkap siswa"
              />
            </Field>
            <Field label="Kelas / peran">
              <input
                value={memberRole}
                onChange={(event) => setMemberRole(event.target.value)}
                placeholder="Kelas dan peran anggota"
              />
            </Field>
          </div>
          <button className="button secondary" onClick={addMember}>
            Tambah anggota
          </button>
        </Card>

        <Card delay={220}>
          <Eyebrow>KHUSUS TIM TERVERIFIKASI</Eyebrow>
          <h2>Robot &amp; API key</h2>
          <StatusBadge>✓ Akses aktif</StatusBadge>
          <div className="form-grid">
            <Field label="robot_code">
              <input readOnly value="NR-114" />
            </Field>
            <Field label="API key">
              <input readOnly value="rr_pilot_••••••••••••7A2F" />
            </Field>
          </div>
          <button className="button primary" onClick={copyApiKey}>
            Salin
          </button>
          <p className="caption">
            Jaga API key timmu. Gunakan untuk mengirim pencapaian robot, bukan untuk menentukan
            hasil resmi.
          </p>
        </Card>

        <Card delay={280}>
          <h2>Pencapaian TITAN</h2>
          <div className="chips">
            {achievementSteps.map((step) => (
              <StatusBadge key={step}>✓ {step}</StatusBadge>
            ))}
          </div>
          <div className="score-row">
            <strong>Run terbaik · 02:14</strong>
            <div>
              <b>112</b>
              <StatusBadge>✓ Resmi</StatusBadge>
            </div>
          </div>
        </Card>

        <MascotMessage image="/assets/team/d8b40.png">
          Robot &amp; API key baru tersedia setelah tim terverifikasi. Perbaiki catatan reviewer,
          lalu kirim ulang datamu.
        </MascotMessage>
      </ContentZone>
      <Toast message={toast} />
    </>
  );
}

function Eyebrow({ children }: { children: React.ReactNode }) {
  return <span className="eyebrow">{children}</span>;
}
function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="field">
      <span>{label}</span>
      {children}
    </label>
  );
}
export function MascotMessage({ image, children }: { image: string; children: React.ReactNode }) {
  return (
    <div className="mascot-message">
      <img src={image} alt="" />
      <strong>{children}</strong>
    </div>
  );
}
