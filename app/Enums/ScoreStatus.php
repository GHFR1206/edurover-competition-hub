<?php

namespace App\Enums;

/**
 * Scoring lifecycle. Approved scores are frozen; edits require an official reopen.
 */
enum ScoreStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Reopened = 'reopened';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Diajukan (belum resmi)',
            self::Approved => 'Disetujui (resmi)',
            self::Reopened => 'Dibuka Ulang',
            self::Rejected => 'Ditolak',
        };
    }

    /** Only submitted and approved scores are ever visible to the owning team. */
    public function isVisibleToTeam(): bool
    {
        return in_array($this, [self::Submitted, self::Approved], true);
    }

    public function isFrozen(): bool
    {
        return $this === self::Approved;
    }
}
