<?php

namespace App\Enums;

/**
 * Progression of a competition category (one per level: SD/SMP/SMA) within an event.
 */
enum CategoryStatus: string
{
    case Registration = 'registration';
    case Scheduled = 'scheduled';
    case Running = 'running';
    case AllRunsFinished = 'all_runs_finished';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Registration => 'Pendaftaran',
            self::Scheduled => 'Terjadwal',
            self::Running => 'Berlangsung',
            self::AllRunsFinished => 'Semua Run Selesai',
            self::Published => 'Dipublikasikan',
        };
    }
}
