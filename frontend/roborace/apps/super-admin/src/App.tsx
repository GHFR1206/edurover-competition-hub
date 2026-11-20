import { useMemo, useState, type CSSProperties, type ReactNode } from "react";
import {
  auditRows,
  events,
  initialTeams,
  initialUsers,
  systems,
  type Page,
  type Team,
  type TeamStatus,
  type User,
} from "./data";
import { Button, Card, Footer, Navbar, PageHero, Status, Toast } from "./components";

const filters: TeamStatus[] = ["Menunggu", "Terverifikasi", "Perlu revisi", "Ditolak"];

export default function App() {
  const [page, setPage] = useState<Page>("ringkasan");
  const [toast, setToast] = useState("");
  const notify = (message: string) => {
    setToast(message);
    window.setTimeout(() => setToast(""), 2400);
  };
  const navigate = (next: Page) => {
    setPage(next);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  return (
    <div className="app">
      <Navbar page={page} onNavigate={navigate} />
      {page === "ringkasan" && <Summary onNavigate={navigate} />}
      {page === "verifikasi" && <Verification notify={notify} />}
      {page === "pengguna" && <Users notify={notify} />}
      {page === "audit" && <Audit />}
      {page === "pengaturan" && <Settings notify={notify} />}
      <Footer />
      <Toast message={toast} />
    </div>
  );
}

function Summary({ onNavigate }: { onNavigate: (page: Page) => void }) {
  return (
    <>
      <main className="page">
        <PageHero page="ringkasan">
          <div className="hero-actions">
            <Button variant="primary" onClick={() => onNavigate("pengguna")}>
              Kelola pengguna
            </Button>
            <Button variant="yellow" onClick={() => onNavigate("audit")}>
              Buka audit log
            </Button>
          </div>
          <small>Sabtu, 14 November 2026 · Pembaruan 11:35 WIB</small>
        </PageHero>
        <div className="content summary-content">
          <div className="stats">
            {[
              ["PENGGUNA AKTIF", "86", "Coach, panitia & juri"],
              ["TIM TERDAFTAR", "48", "44 terverifikasi"],
              ["EVENT BERJALAN", "01", "Seri Pilot 2026"],
              ["PERLU VERIFIKASI", "04", "Bantu tim masuk arena"],
            ].map((item, i) => (
              <Card
                key={item[0]}
                className="stat"
                style={{ "--delay": `${i * 80}ms` } as CSSProperties}
              >
                <small>{item[0]}</small>
                <strong>{item[1]}</strong>
                <span>{item[2]}</span>
              </Card>
            ))}
          </div>
          <div className="monitor-grid">
            <Card>
              <h2>Monitoring Event</h2>
              {events.map((event) => (
                <Row key={event.name} title={event.name} detail={event.detail}>
                  <Status tone={event.tone}>{event.status}</Status>
                </Row>
              ))}
            </Card>
            <Card>
              <small className="eyebrow">SEMUA SISTEM DALAM PANTAUAN</small>
              <h2>Monitoring Sistem</h2>
              {systems.map(([name, detail]) => (
                <Row key={name} title={name} detail={detail}>
                  <Status tone="green">● Normal</Status>
                </Row>
              ))}
            </Card>
          </div>
          <Card>
            <h2>Audit Terbaru</h2>
            {auditRows.slice(0, 3).map((row) => (
              <Row
                key={row.action}
                title={row.action}
                detail={`${row.actor} · ${row.change} · ${row.time} WIB`}
              >
                <Status>{row.category}</Status>
              </Row>
            ))}
          </Card>
        </div>
      </main>
    </>
  );
}

function Row({ title, detail, children }: { title: string; detail: string; children?: ReactNode }) {
  return (
    <div className="data-row">
      <div>
        <strong>{title}</strong>
        <span>{detail}</span>
      </div>
      {children}
    </div>
  );
}

function Verification({ notify }: { notify: (message: string) => void }) {
  const [teams, setTeams] = useState(initialTeams);
  const [tab, setTab] = useState<TeamStatus>("Menunggu");
  const [selectedId, setSelectedId] = useState(1);
  const [note, setNote] = useState("");
  const selected = teams.find((team) => team.id === selectedId) || teams[0];
  const list = teams.filter((team) => team.status === tab);
  const update = (status: TeamStatus) => {
    if ((status === "Ditolak" || status === "Perlu revisi") && !note.trim())
      return notify("Isi catatan sebelum melanjutkan.");
    setTeams((current) =>
      current.map((team) => (team.id === selected.id ? { ...team, status } : team)),
    );
    setTab(status);
    setNote("");
    notify(
      status === "Terverifikasi"
        ? "Tim berhasil disetujui."
        : `Tim dipindahkan ke ${status.toLowerCase()}.`,
    );
  };
  return (
    <main className="page">
      <PageHero page="verifikasi" />
      <div className="content decorated">
        <div className="chips">
          {filters.map((item) => (
            <button
              className={tab === item ? "selected" : ""}
              onClick={() => setTab(item)}
              key={item}
            >
              {item}
              {item === "Menunggu" ? " · 4" : item === "Terverifikasi" ? " · 44" : ""}
            </button>
          ))}
        </div>
        <Card className="review-card">
          <small className="eyebrow">PENGAJUAN 14 NOVEMBER 2026 · 08:45 WIB</small>
          <h2>{selected.name}</h2>
          <Status tone="yellow">Menunggu verifikasi</Status>
          <div className="team-detail">
            <p>
              Level {selected.category} · {selected.detail}
              <br />
              Coach: {selected.coach}
              <br />
              Email: {selected.email}
            </p>
            <p>
              2 anggota: {selected.members}
              <br />
              Surat sekolah: surat-{selected.name.split(" ")[0].toLowerCase()}.pdf
              <br />
              API Robot: -
            </p>
          </div>
          <Button>Lihat dokumen</Button>
          <label>Catatan penolakan / revisi *</label>
          <textarea
            value={note}
            onChange={(e) => setNote(e.target.value)}
            placeholder="Surat pendamping belum ditandatangani kepala sekolah. Mohon unggah kembali dokumen yang lengkap."
          />
          <small>* Wajib diisi</small>
          <div className="button-row">
            <Button variant="primary" onClick={() => update("Terverifikasi")}>
              Setujui
            </Button>
            <Button variant="danger" onClick={() => update("Ditolak")}>
              Tolak
            </Button>
            <Button variant="yellow" onClick={() => update("Perlu revisi")}>
              Minta revisi
            </Button>
          </div>
        </Card>
        <Card>
          <h2>{tab === "Terverifikasi" ? "Tim terverifikasi" : `Daftar ${tab.toLowerCase()}`}</h2>
          {list.length ? (
            list.map((team) => (
              <Row
                key={team.id}
                title={team.name}
                detail={`Kategori ${team.category} · ${team.detail}`}
              >
                <div className="row-actions">
                  <Status tone={team.status === "Terverifikasi" ? "green" : "yellow"}>
                    {team.status === "Terverifikasi" ? "✓ Terverifikasi" : team.status}
                  </Status>
                  {team.id !== selectedId && (
                    <Button onClick={() => setSelectedId(team.id)}>Periksa</Button>
                  )}
                </div>
              </Row>
            ))
          ) : (
            <p className="empty">Belum ada tim pada daftar ini.</p>
          )}
        </Card>
      </div>
    </main>
  );
}

function Users({ notify }: { notify: (message: string) => void }) {
  const [users, setUsers] = useState(initialUsers);
  const [filter, setFilter] = useState("Semua");
  const [search, setSearch] = useState("");
  const [invite, setInvite] = useState("");
  const [editing, setEditing] = useState<User | null>(null);
  const shown = users.filter(
    (user) =>
      (filter === "Semua" || (filter === "Aktif") === user.active) &&
      `${user.name} ${user.email}`.toLowerCase().includes(search.toLowerCase()),
  );
  const inviteUser = () => {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(invite)) return notify("Format email belum valid.");
    notify(`Undangan dikirim ke ${invite}.`);
    setInvite("");
  };
  return (
    <main className={`page ${editing ? "modal-open" : ""}`}>
      <PageHero page="pengguna" />
      <div className="content decorated">
        <Card>
          <h2>Undang Pengguna</h2>
          <div className="invite-grid">
            <label>
              Email
              <input
                value={invite}
                onChange={(e) => setInvite(e.target.value)}
                placeholder="maya@panitia.contoh"
              />
            </label>
            <label>
              Peran
              <select>
                <option>Event Admin</option>
                <option>Juri</option>
                <option>Operator Arena</option>
              </select>
            </label>
          </div>
          <Button variant="yellow" onClick={inviteUser}>
            Undang Pengguna
          </Button>
        </Card>
        <div className="search-line">
          <label>
            Cari pengguna
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nama atau email..."
            />
          </label>
          <div className="chips">
            {["Semua", "Aktif", "Nonaktif"].map((item) => (
              <button
                key={item}
                className={filter === item ? "selected" : ""}
                onClick={() => setFilter(item)}
              >
                {item}
              </button>
            ))}
          </div>
        </div>
        <Card>
          <small className="eyebrow">
            {users.filter((u) => u.active).length} AKUN AKTIF · DATA TERKINI
          </small>
          <h2>Akun pengguna</h2>
          {shown.map((user) => (
            <div className="user-row" key={user.id}>
              <div className="user-head">
                <div>
                  <strong>
                    {user.name} · {user.role}
                  </strong>
                  <span>{user.email}</span>
                </div>
                <Status tone={user.active ? "green" : "red"}>
                  {user.active ? "Aktif" : "Nonaktif"}
                </Status>
              </div>
              <div className="button-row">
                <Button onClick={() => setEditing(user)}>Edit data</Button>
                <Button
                  variant={user.active ? "danger" : "primary"}
                  onClick={() =>
                    setUsers((current) =>
                      current.map((u) => (u.id === user.id ? { ...u, active: !u.active } : u)),
                    )
                  }
                >
                  {user.active ? "Nonaktifkan" : "Aktifkan"}
                </Button>
              </div>
            </div>
          ))}
        </Card>
      </div>
      {editing && (
        <EditModal
          user={editing}
          onClose={() => setEditing(null)}
          onSave={(updated) => {
            setUsers((current) => current.map((u) => (u.id === updated.id ? updated : u)));
            setEditing(null);
            notify("Perubahan pengguna berhasil disimpan.");
          }}
        />
      )}
    </main>
  );
}

function EditModal({
  user,
  onClose,
  onSave,
}: {
  user: User;
  onClose: () => void;
  onSave: (user: User) => void;
}) {
  const [form, setForm] = useState(user);
  return (
    <div className="modal-backdrop" onMouseDown={(e) => e.target === e.currentTarget && onClose()}>
      <div className="modal" role="dialog" aria-modal="true">
        <button className="close" onClick={onClose} aria-label="Tutup">
          <img src="/assets/8f70f.svg" alt="" />
        </button>
        <h2>Edit data pengguna</h2>
        <p>Perbarui identitas dan akses akun pengguna.</p>
        <div className="profile-strip">
          <b>
            {form.name
              .split(" ")
              .map((x) => x[0])
              .slice(0, 2)
              .join("")}
          </b>
          <div>
            <strong>{form.name}</strong>
            <span>{form.role}</span>
          </div>
          <Status tone={form.active ? "green" : "red"}>
            ● {form.active ? "Aktif" : "Nonaktif"}
          </Status>
        </div>
        <div className="form-grid">
          <label>
            Nama lengkap *
            <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
          </label>
          <label>
            Email *
            <input
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
          </label>
          <label>
            Peran
            <select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}>
              <option>Event Admin</option>
              <option>Super Admin</option>
              <option>Operator Arena</option>
              <option>Juri</option>
              <option>Chief Judge</option>
              <option>Coach</option>
            </select>
          </label>
          <label>
            Status akun
            <select
              value={String(form.active)}
              onChange={(e) => setForm({ ...form, active: e.target.value === "true" })}
            >
              <option value="true">● Aktif</option>
              <option value="false">● Nonaktif</option>
            </select>
          </label>
        </div>
        <div className="notice">ⓘ Perubahan akses diterapkan saat disimpan.</div>
        <div className="modal-actions">
          <Button onClick={onClose}>Batal</Button>
          <Button variant="yellow" onClick={() => onSave(form)}>
            Simpan perubahan
          </Button>
        </div>
      </div>
    </div>
  );
}

function Audit() {
  const [category, setCategory] = useState("Semua");
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(0);
  const filtered = useMemo(
    () =>
      auditRows.filter(
        (row) =>
          (category === "Semua" || row.category === category) &&
          Object.values(row).join(" ").toLowerCase().includes(search.toLowerCase()),
      ),
    [category, search],
  );
  const rows = filtered.slice(page * 8, page * 8 + 8);
  return (
    <main className="page">
      <PageHero page="audit" />
      <div className="content decorated">
        <div className="chips">
          {["Semua", "Event", "Skor", "Tim", "Peran", "Sistem"].map((item) => (
            <button
              key={item}
              className={category === item ? "selected" : ""}
              onClick={() => {
                setCategory(item);
                setPage(0);
              }}
            >
              {item}
            </button>
          ))}
        </div>
        <div className="search-line two">
          <label>
            Cari aktivitas
            <input
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(0);
              }}
              placeholder="Nama, tim, event, atau tindakan..."
            />
          </label>
          <label>
            Rentang waktu
            <input value="14 November 2026" readOnly />
          </label>
        </div>
        <Card>
          <small className="eyebrow">14 NOVEMBER 2026 · WIB</small>
          <h2>Riwayat aktivitas</h2>
          <div className="table">
            <div className="table-head">
              <span>AKSI</span>
              <span>PELAKU</span>
              <span>OBJEK</span>
              <span>PERUBAHAN</span>
              <span>WAKTU</span>
            </div>
            {rows.map((row, i) => (
              <div className="table-row" key={`${row.action}-${i}`}>
                <strong>{row.action}</strong>
                <span>{row.actor}</span>
                <span>{row.object}</span>
                <span>{row.change}</span>
                <b>{row.time}</b>
              </div>
            ))}
          </div>
          <div className="pagination">
            <Status>
              {Math.min((page + 1) * 8, filtered.length)} dari {filtered.length} aktivitas
            </Status>
            <Button disabled={page === 0} onClick={() => setPage((p) => p - 1)}>
              Sebelumnya
            </Button>
            <Button
              disabled={(page + 1) * 8 >= filtered.length}
              onClick={() => setPage((p) => p + 1)}
            >
              Berikutnya
            </Button>
          </div>
        </Card>
        <p className="audit-note">
          Riwayat tidak dapat dihapus dari antarmuka. Koreksi menyimpan nilai lama, nilai baru,
          serta alasan.
        </p>
      </div>
    </main>
  );
}

function Settings({ notify }: { notify: (message: string) => void }) {
  const initial = { verification: true, method: "Hybrid", service: "API robot EduRover" };
  const [settings, setSettings] = useState(initial);
  const descriptions: Record<string, string> = {
    Manual: "Nilai diisi langsung oleh juri dan diputuskan oleh Chief Judge.",
    Hybrid:
      "Hybrid menggabungkan pencapaian robot dengan rata-rata nilai manual juri. Semua pencapaian tetap memerlukan keputusan Chief Judge.",
    Telemetri: "Pencapaian dikirim otomatis oleh robot untuk dipantau oleh juri.",
  };
  return (
    <main className="page">
      <PageHero page="pengaturan" />
      <div className="content decorated settings">
        <Card>
          <h2>Keamanan akun</h2>
          <div className="setting-row">
            <div>
              <strong>Wajibkan verifikasi email</strong>
              <p>Akun baru harus memverifikasi email sebelum mengirim data tim untuk ditinjau.</p>
              <Status tone="green">{settings.verification ? "Aktif" : "Nonaktif"}</Status>
            </div>
            <button
              className={`toggle ${settings.verification ? "on" : ""}`}
              aria-label="Ubah verifikasi email"
              onClick={() => setSettings({ ...settings, verification: !settings.verification })}
            >
              {settings.verification ? <img src="/assets/56c18.svg" alt="" /> : <i />}
            </button>
          </div>
        </Card>
        <Card>
          <h2>Metode penilaian default</h2>
          <div className="chips">
            {["Manual", "Hybrid", "Telemetri"].map((item) => (
              <button
                className={settings.method === item ? "selected" : ""}
                onClick={() => setSettings({ ...settings, method: item })}
                key={item}
              >
                {item}
              </button>
            ))}
          </div>
          <p>{descriptions[settings.method]}</p>
          <small>
            Manual: nilai diisi juri · Telemetri: pencapaian dikirim robot · Hybrid: keduanya
            digunakan bersama.
          </small>
          <hr />
          <label>
            Nama layanan robot
            <input
              value={settings.service}
              onChange={(e) => setSettings({ ...settings, service: e.target.value })}
            />
          </label>
          <small>START 0 · CP1/CP2/CP3 masing-masing 20 · FINISH 40 · rata-rata manual 0–20.</small>
        </Card>
        <div className="button-row">
          <Button variant="yellow" onClick={() => notify("Pengaturan berhasil disimpan.")}>
            Simpan pengaturan
          </Button>
          <Button
            onClick={() => {
              setSettings(initial);
              notify("Perubahan dibatalkan.");
            }}
          >
            Batalkan perubahan
          </Button>
        </div>
        <div className="mascot-note">
          <img src="/assets/17d7a.png" alt="" />
          <strong>
            Pengaturan default berlaku untuk event baru. Ruleset event berjalan tetap terkunci agar
            kompetisi adil.
          </strong>
        </div>
      </div>
    </main>
  );
}
