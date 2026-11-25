<?php

namespace App\Enums;

/**
 * Registration / verification lifecycle for a team entry.
 *
 * submitted -> under_review -> verified
 *                           -> rejected  (ditolak permanen)
 *                           -> revision  (dikembalikan untuk diperbaiki)
 * rejected|revision -> submitted (coach mengajukan ulang)
 */
enum RegistrationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Revision = 'revision';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Diajukan',
            self::UnderReview => 'Ditinjau',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
            self::Revision => 'Perlu Revisi',
            self::Withdrawn => 'Dibatalkan',
        };
    }

    /**
     * The coach may still edit the team and resubmit in these states.
     * "Perlu Revisi" exists so the committee can send an entry back without
     * labelling it as a final rejection.
     */
    public function isEditableByCoach(): bool
    {
        return in_array($this, [self::Draft, self::Rejected, self::Revision], true);
    }

    /** Statuses a committee member can still act on (approve/reject/revision). */
    public function isAwaitingDecision(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview], true);
    }
}
