import { useEffect, useMemo, useRef, useState } from "react";

const MONTHS = [
  "Januari",
  "Februari",
  "Maret",
  "April",
  "Mei",
  "Juni",
  "Juli",
  "Agustus",
  "September",
  "Oktober",
  "November",
  "Desember",
];
const DAYS = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];
export const MIN_RERUN_DATE = "2026-11-14";

function fromIso(value: string) {
  const [year, month, day] = value.split("-").map(Number);
  return new Date(year, month - 1, day);
}

function toIso(date: Date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
}

export function formatIndonesianDate(value: string) {
  const date = fromIso(value);
  return `${date.getDate()} ${MONTHS[date.getMonth()]} ${date.getFullYear()}`;
}

function sameDay(a: Date, b: Date) {
  return (
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate()
  );
}

function CalendarIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <path d="M7 3v3m10-3v3M4.5 9h15M6 5h12a2 2 0 0 1 2 2v12H4V7a2 2 0 0 1 2-2Z" />
    </svg>
  );
}

function ClockIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="12" cy="12" r="8.5" />
      <path d="M12 7.5V12l3 2" />
    </svg>
  );
}

function useOutsideClose(ref: React.RefObject<HTMLDivElement | null>, close: () => void) {
  useEffect(() => {
    const handlePointer = (event: PointerEvent) => {
      if (!ref.current?.contains(event.target as Node)) close();
    };
    document.addEventListener("pointerdown", handlePointer);
    return () => document.removeEventListener("pointerdown", handlePointer);
  }, [close, ref]);
}

export function DatePicker({
  value,
  onChange,
}: {
  value: string;
  onChange: (value: string) => void;
}) {
  const selected = fromIso(value);
  const minimum = fromIso(MIN_RERUN_DATE);
  const [open, setOpen] = useState(false);
  const [viewDate, setViewDate] = useState(
    new Date(selected.getFullYear(), selected.getMonth(), 1),
  );
  const rootRef = useRef<HTMLDivElement>(null);
  useOutsideClose(rootRef, () => setOpen(false));

  const calendarDays = useMemo(() => {
    const first = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
    const mondayOffset = (first.getDay() + 6) % 7;
    const start = new Date(first);
    start.setDate(first.getDate() - mondayOffset);
    return Array.from({ length: 42 }, (_, index) => {
      const date = new Date(start);
      date.setDate(start.getDate() + index);
      return date;
    });
  }, [viewDate]);

  const moveSelection = (days: number) => {
    const next = fromIso(value);
    next.setDate(next.getDate() + days);
    if (next < minimum) return;
    onChange(toIso(next));
    setViewDate(new Date(next.getFullYear(), next.getMonth(), 1));
    setOpen(true);
  };

  return (
    <div className="picker" ref={rootRef}>
      <button
        type="button"
        className="picker-trigger"
        aria-haspopup="dialog"
        aria-expanded={open}
        onClick={() => setOpen((current) => !current)}
        onKeyDown={(event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            setOpen((current) => !current);
          } else if (event.key === "ArrowRight") {
            event.preventDefault();
            moveSelection(1);
          } else if (event.key === "ArrowLeft") {
            event.preventDefault();
            moveSelection(-1);
          } else if (event.key === "ArrowDown") {
            event.preventDefault();
            moveSelection(7);
          } else if (event.key === "ArrowUp") {
            event.preventDefault();
            moveSelection(-7);
          } else if (event.key === "Escape") {
            setOpen(false);
          }
        }}
      >
        <span>{formatIndonesianDate(value)}</span>
        <CalendarIcon />
      </button>
      {open && (
        <div className="calendar-popover" role="dialog" aria-label="Pilih tanggal re-run">
          <div className="calendar-header">
            <button
              type="button"
              aria-label="Bulan sebelumnya"
              onClick={() =>
                setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1))
              }
            >
              ‹
            </button>
            <strong>
              {MONTHS[viewDate.getMonth()]} {viewDate.getFullYear()}
            </strong>
            <button
              type="button"
              aria-label="Bulan berikutnya"
              onClick={() =>
                setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1))
              }
            >
              ›
            </button>
          </div>
          <div className="calendar-grid calendar-weekdays">
            {DAYS.map((day) => (
              <span key={day}>{day}</span>
            ))}
          </div>
          <div className="calendar-grid">
            {calendarDays.map((date) => {
              const disabled = date < minimum;
              const isSelected = sameDay(date, selected);
              const isToday = sameDay(date, new Date());
              const outside = date.getMonth() !== viewDate.getMonth();
              return (
                <button
                  type="button"
                  key={toIso(date)}
                  disabled={disabled}
                  className={`${isSelected ? "selected" : ""} ${isToday ? "today" : ""} ${outside ? "outside" : ""}`}
                  aria-pressed={isSelected}
                  onClick={() => {
                    onChange(toIso(date));
                    setOpen(false);
                  }}
                >
                  {date.getDate()}
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}

export const TIME_OPTIONS = Array.from({ length: 35 }, (_, index) => {
  const minutes = 8 * 60 + index * 15;
  return `${String(Math.floor(minutes / 60)).padStart(2, "0")}:${String(minutes % 60).padStart(2, "0")}`;
});

export function TimePicker({
  value,
  onChange,
}: {
  value: string;
  onChange: (value: string) => void;
}) {
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);
  const selectedRef = useRef<HTMLButtonElement>(null);
  useOutsideClose(rootRef, () => setOpen(false));

  useEffect(() => {
    if (open) selectedRef.current?.scrollIntoView({ block: "nearest" });
  }, [open]);

  const moveSelection = (direction: number) => {
    const index = Math.max(0, TIME_OPTIONS.indexOf(value));
    const next = Math.min(TIME_OPTIONS.length - 1, Math.max(0, index + direction));
    onChange(TIME_OPTIONS[next]);
    setOpen(true);
  };

  return (
    <div className="picker" ref={rootRef}>
      <button
        type="button"
        className="picker-trigger"
        aria-haspopup="listbox"
        aria-expanded={open}
        onClick={() => setOpen((current) => !current)}
        onKeyDown={(event) => {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            setOpen((current) => !current);
          } else if (event.key === "ArrowDown" || event.key === "ArrowRight") {
            event.preventDefault();
            moveSelection(1);
          } else if (event.key === "ArrowUp" || event.key === "ArrowLeft") {
            event.preventDefault();
            moveSelection(-1);
          } else if (event.key === "Escape") {
            setOpen(false);
          }
        }}
      >
        <span>{value} WIB</span>
        <ClockIcon />
      </button>
      {open && (
        <div className="time-popover" role="listbox" aria-label="Pilih jam re-run">
          {TIME_OPTIONS.map((time) => (
            <button
              type="button"
              role="option"
              aria-selected={time === value}
              className={time === value ? "selected" : ""}
              ref={time === value ? selectedRef : undefined}
              key={time}
              onClick={() => {
                onChange(time);
                setOpen(false);
              }}
            >
              {time} WIB
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
