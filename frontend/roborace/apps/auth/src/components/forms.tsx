import { type FormEvent, useState } from "react";
import { passwordRules, type PageName } from "../data/authData";

type NavigateProps = { onNavigate: (page: PageName) => void };

type FieldProps = {
  label: string;
  name: string;
  type?: string;
  value: string;
  placeholder?: string;
  error?: string;
  onChange: (value: string) => void;
};

function Field({ label, name, type = "text", value, placeholder, error, onChange }: FieldProps) {
  return (
    <label className="field">
      <span>{label}</span>
      <input
        name={name}
        type={type}
        value={value}
        placeholder={placeholder}
        aria-invalid={Boolean(error)}
        aria-describedby={error ? `${name}-error` : undefined}
        onChange={(event) => onChange(event.target.value)}
      />
      {error && (
        <small className="field-error" id={`${name}-error`}>
          {error}
        </small>
      )}
    </label>
  );
}

type PasswordFieldProps = Omit<FieldProps, "type">;

function PasswordField(props: PasswordFieldProps) {
  const [visible, setVisible] = useState(false);
  return (
    <label className="field">
      <span>{props.label}</span>
      <div className={`password-input ${props.error ? "input-error" : ""}`}>
        <input
          name={props.name}
          type={visible ? "text" : "password"}
          value={props.value}
          placeholder={props.placeholder}
          aria-invalid={Boolean(props.error)}
          onChange={(event) => props.onChange(event.target.value)}
        />
        <button
          type="button"
          className="eye-button"
          onClick={() => setVisible((current) => !current)}
          aria-label={visible ? "Sembunyikan kata sandi" : "Tampilkan kata sandi"}
        >
          <img src={visible ? "/assets/e19c8.svg" : "/assets/79260.svg"} alt="" />
        </button>
      </div>
      {props.error && <small className="field-error">{props.error}</small>}
    </label>
  );
}

function SubmitButton({ children, yellow = false }: { children: string; yellow?: boolean }) {
  return (
    <button className={`submit-button ${yellow ? "submit-button--yellow" : ""}`} type="submit">
      {children}
    </button>
  );
}

export function LoginForm({ onNavigate }: NavigateProps) {
  const [email, setEmail] = useState("coach@sekolah.contoh");
  const [password, setPassword] = useState("");
  const [remember, setRemember] = useState(true);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const submit = (event: FormEvent) => {
    event.preventDefault();
    const nextErrors: Record<string, string> = {};
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) nextErrors.email = "Format email belum benar.";
    if (!password) nextErrors.password = "Kata sandi wajib diisi.";
    setErrors(nextErrors);
  };

  return (
    <form className="auth-card login-card" onSubmit={submit} noValidate>
      <span className="card-kicker">MULAI DARI SINI</span>
      <h2>Masuk ke RoboRace</h2>
      <p className="card-description">Senang melihatmu kembali di lintasan!</p>
      <Field label="Email" name="email" value={email} onChange={setEmail} error={errors.email} />
      <PasswordField
        label="Kata sandi"
        name="password"
        value={password}
        onChange={setPassword}
        error={errors.password}
      />
      <div className="form-options">
        <label className="remember">
          <input
            type="checkbox"
            checked={remember}
            onChange={(event) => setRemember(event.target.checked)}
          />
          <span aria-hidden="true">✓</span>
          Ingat saya
        </label>
        <button type="button" className="text-button" onClick={() => onNavigate("forgot")}>
          Lupa kata sandi?
        </button>
      </div>
      <SubmitButton>Masuk</SubmitButton>
      <p className="switch-page">
        Belum punya akun?{" "}
        <button type="button" onClick={() => onNavigate("register")}>
          Daftar sekolah →
        </button>
      </p>
      <div className="dotted-divider" />
    </form>
  );
}

export function RegisterForm({ onNavigate }: NavigateProps) {
  const [name, setName] = useState("Rani Wulandari");
  const [school, setSchool] = useState("SMP Garuda A");
  const [email, setEmail] = useState("rani@garuda.contoh");
  const [password, setPassword] = useState("RoboRace2026");
  const [errors, setErrors] = useState<Record<string, string>>({});
  const passedRules = passwordRules.filter((rule) => rule.test(password)).length;

  const submit = (event: FormEvent) => {
    event.preventDefault();
    const nextErrors: Record<string, string> = {};
    if (!name.trim()) nextErrors.name = "Nama lengkap wajib diisi.";
    if (!school.trim()) nextErrors.school = "Sekolah atau institusi wajib diisi.";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) nextErrors.email = "Format email belum benar.";
    if (passedRules < 4) nextErrors.password = "Kata sandi belum memenuhi semua syarat.";
    setErrors(nextErrors);
    if (!Object.keys(nextErrors).length) onNavigate("login");
  };

  return (
    <form className="auth-card register-card" onSubmit={submit} noValidate>
      <span className="card-kicker">LANGKAH PERTAMA MENUJU ARENA</span>
      <h2>Daftar sekolah</h2>
      <Field label="Nama lengkap" name="name" value={name} onChange={setName} error={errors.name} />
      <Field
        label="Sekolah / Institusi"
        name="school"
        value={school}
        onChange={setSchool}
        error={errors.school}
      />
      <Field
        label="Email"
        name="register-email"
        type="email"
        value={email}
        onChange={setEmail}
        error={errors.email}
      />
      <PasswordField
        label="Kata sandi"
        name="register-password"
        value={password}
        onChange={setPassword}
        error={errors.password}
      />
      <div className="strength" aria-label={`Kekuatan kata sandi ${passedRules} dari 4`}>
        {["Lemah", "Cukup", "Baik", "Kuat"].map((label, index) => (
          <div key={label}>
            <i className={index < passedRules ? "active" : ""} />
            <span>{label}</span>
          </div>
        ))}
      </div>
      <div className="password-rules">
        {passwordRules.map((rule) => (
          <span key={rule.label}>
            {rule.test(password) ? "✓" : "○"} {rule.label}
          </span>
        ))}
        <span>○ Simbol (! @ # $ %) - disarankan</span>
      </div>
      <SubmitButton yellow>Daftar & lanjut</SubmitButton>
      <p className="terms">
        Dengan mendaftar, kamu menyetujui aturan kompetisi dan penggunaan data untuk verifikasi
        sekolah.
      </p>
      <p className="switch-page">
        Sudah punya akun?{" "}
        <button type="button" onClick={() => onNavigate("login")}>
          Masuk →
        </button>
      </p>
    </form>
  );
}

export function ForgotPasswordForm({
  onNavigate,
  onSent,
}: NavigateProps & { onSent: (email: string) => void }) {
  const [email, setEmail] = useState("");
  const [error, setError] = useState("");

  const submit = (event: FormEvent) => {
    event.preventDefault();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setError("Format email belum benar.");
      return;
    }
    setError("");
    onSent(email);
  };

  return (
    <form className="auth-card forgot-card" onSubmit={submit} noValidate>
      <h2>Lupa kata sandi</h2>
      <p className="card-description">Tautan berlaku 30 menit.</p>
      <Field
        label="Email"
        name="forgot-email"
        type="email"
        value={email}
        placeholder="nama@sekolah.sch.id"
        onChange={setEmail}
        error={error}
      />
      <SubmitButton>Kirim tautan reset</SubmitButton>
      <button type="button" className="text-button back-button" onClick={() => onNavigate("login")}>
        ← Kembali ke Masuk
      </button>
    </form>
  );
}

export function CheckEmailForm({ email, onNavigate }: NavigateProps & { email: string }) {
  return (
    <div className="auth-card check-card">
      <h2>Cek emailmu 📬</h2>
      <p className="card-description">Jika {email} terdaftar, tautan reset sudah kami kirim.</p>
      <div className="success-note">Belum masuk? Cek folder spam dalam 2 menit.</div>
      <button className="submit-button" type="button" onClick={() => onNavigate("login")}>
        Kembali ke Masuk
      </button>
    </div>
  );
}
