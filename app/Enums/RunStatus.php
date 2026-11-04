<?php

namespace App\Enums;

/**
 * Lifecycle of a single competition run on an arena.
 */
enum RunStatus: string
{
    case Scheduled = 'scheduled';
    case Called = 'called';
    case Ready = 'ready';
    case Running = 'running';
    case Finished = 'finished';
    case Dnf = 'dnf';
    case Dq = 'dq';
    case NoShow = 'no_show';
    case ReRun = 're_run';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Called => 'Dipanggil',
            self::Ready => 'Siap',
            self::Running => 'Berjalan',
            self::Finished => 'Selesai',
            self::Dnf => 'DNF',
            self::Dq => 'DQ',
            self::NoShow => 'Tidak Hadir',
            self::ReRun => 'Re-run',
        };
    }

    /** True when the run has reached a terminal outcome for scoring purposes. */
    public function isSettled(): bool
    {
        return in_array($this, [self::Finished, self::Dnf, self::Dq, self::NoShow], true);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Called, self::Ready, self::Running], true);
    }
}
