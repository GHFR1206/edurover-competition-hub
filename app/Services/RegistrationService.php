<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Registration (team entry) lifecycle. Event Admin / Super Admin verify.
 *
 * submitted -> under_review -> verified
 *                           -> rejected (reason required)
 * Teams are mirrored with the same status so downstream (heat scheduling)
 * can filter on team.status without joining registrations.
 */
class RegistrationService
{
    /** @return array<int, RegistrationStatus> statuses allowed to transition onward */
    public function allowedTransitions(Registration $registration): array
    {
        return match ($registration->status) {
            RegistrationStatus::Draft => [RegistrationStatus::Submitted],
            RegistrationStatus::Submitted => [RegistrationStatus::UnderReview, RegistrationStatus::Verified, RegistrationStatus::Rejected],
            RegistrationStatus::UnderReview => [RegistrationStatus::Verified, RegistrationStatus::Rejected],
            RegistrationStatus::Rejected => [RegistrationStatus::Submitted],
            RegistrationStatus::Verified => [RegistrationStatus::Withdrawn],
            RegistrationStatus::Withdrawn => [],
        };
    }

    public function canTransition(Registration $registration, RegistrationStatus $to): bool
    {
        return in_array($to, $this->allowedTransitions($registration), true);
    }

    /** @return array<int, string> blocking reasons for verification; empty = ok */
    public function verifyBlockers(Registration $registration): array
    {
        $reasons = [];

        if (! $this->canTransition($registration, RegistrationStatus::Verified)) {
            $reasons[] = "Status saat ini ({$registration->status->label()}) tidak dapat diverifikasi.";
        }

        if (! $registration->consent_parent) {
            $reasons[] = 'Persetujuan orang tua belum tercatat.';
        }

        if ($registration->team && $registration->team->participants()->count() === 0) {
            $reasons[] = 'Tim belum memiliki anggota.';
        }

        return $reasons;
    }

    public function submit(Registration $registration): void
    {
        $this->transition($registration, RegistrationStatus::Submitted, ['submitted_at' => now()]);
    }

    public function startReview(Registration $registration): void
    {
        $this->transition($registration, RegistrationStatus::UnderReview);
    }

    /**
     * @return array<int, string> blockers; empty on success
     */
    public function verify(Registration $registration, User $verifier): array
    {
        $blockers = $this->verifyBlockers($registration);

        if ($blockers !== []) {
            return $blockers;
        }

        $this->transition($registration, RegistrationStatus::Verified, [
            'verified_at' => now(),
            'verified_by' => $verifier->id,
            'rejection_reason' => null,
        ]);

        return [];
    }

    public function reject(Registration $registration, User $verifier, string $reason): void
    {
        if (trim($reason) === '') {
            throw new RuntimeException('Alasan penolakan wajib diisi.');
        }

        if (! $this->canTransition($registration, RegistrationStatus::Rejected)) {
            throw new RuntimeException("Status {$registration->status->label()} tidak dapat ditolak.");
        }

        $this->transition($registration, RegistrationStatus::Rejected, [
            'verified_by' => $verifier->id,
            'rejection_reason' => $reason,
        ]);
    }

    public function withdraw(Registration $registration): void
    {
        $this->transition($registration, RegistrationStatus::Withdrawn);
    }

    /** @param array<string, mixed> $extra */
    private function transition(Registration $registration, RegistrationStatus $to, array $extra = []): void
    {
        if (! $this->canTransition($registration, $to) && $registration->status !== $to) {
            throw new RuntimeException(
                "Transisi {$registration->status->label()} → {$to->label()} tidak diizinkan."
            );
        }

        DB::transaction(function () use ($registration, $to, $extra) {
            $registration->fill(['status' => $to] + $extra);
            $registration->save();

            // Keep team.status in sync so scheduling can rely on it.
            $registration->team?->update(['status' => $to]);
        });
    }
}
