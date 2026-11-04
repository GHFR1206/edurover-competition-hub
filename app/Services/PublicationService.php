<?php

namespace App\Services;

use App\Enums\CategoryStatus;
use App\Enums\ScoreStatus;
use App\Models\Category;
use App\Models\CategoryPublication;
use App\Models\Score;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Controls the Approve (Chief Judge) and Publish (separate step) lifecycle.
 * Publish is blocked until every run is settled and every score is approved.
 */
class PublicationService
{
    /**
     * Reasons a category cannot yet be published. Empty array = publishable.
     *
     * @return array<int, string>
     */
    public function blockingReasons(Category $category): array
    {
        $reasons = [];

        if ($category->isPublished()) {
            $reasons[] = 'Kategori sudah dipublikasikan.';
        }

        $runs = $category->runs()->with('scores')->get();

        if ($runs->isEmpty()) {
            $reasons[] = 'Belum ada run yang terjadwal pada kategori ini.';
        }

        $unfinished = $runs->filter(fn ($run) => ! $run->status->isSettled());

        if ($unfinished->isNotEmpty()) {
            $reasons[] = "Masih ada {$unfinished->count()} run yang belum selesai.";
        }

        $unapproved = Score::whereIn('run_id', $runs->pluck('id'))
            ->where('status', '!=', ScoreStatus::Approved->value)
            ->count();

        if ($unapproved > 0) {
            $reasons[] = "Masih ada {$unapproved} skor yang belum disetujui Chief Judge.";
        }

        $runsWithoutScore = $runs->filter(
            fn ($run) => $run->status->isSettled() && $run->scores->isEmpty()
        );

        if ($runsWithoutScore->isNotEmpty()) {
            $reasons[] = "Ada {$runsWithoutScore->count()} run selesai tanpa skor.";
        }

        return $reasons;
    }

    public function canPublish(Category $category): bool
    {
        return $this->blockingReasons($category) === [];
    }

    /** @return array<int, string> the blocking reasons, empty on success */
    public function publish(Category $category, User $chief): array
    {
        $reasons = $this->blockingReasons($category);

        if ($reasons !== []) {
            return $reasons;
        }

        DB::transaction(function () use ($category, $chief) {
            $category->status = CategoryStatus::Published;
            $category->save();

            CategoryPublication::updateOrCreate(
                ['category_id' => $category->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'published_at' => now(),
                    'published_by' => $chief->id,
                ],
            );

            $category->writeAudit('updated', null, ['status' => 'published'], 'Publikasi hasil kategori');
        });

        return [];
    }

    /** Approve a submitted score (maker-checker enforced in the controller/policy). */
    public function approve(Score $score, User $chief): void
    {
        if ($score->status === ScoreStatus::Approved) {
            throw new RuntimeException('Skor sudah disetujui.');
        }

        DB::transaction(function () use ($score, $chief) {
            $score->status = ScoreStatus::Approved;
            $score->approved_at = now();
            $score->approved_by = $chief->id;
            $score->save();

            $score->approvals()->create([
                'run_id' => $score->run_id,
                'decision' => 'approved',
                'decided_by' => $chief->id,
                'decided_at' => now(),
            ]);

            $score->writeAudit('updated', null, ['status' => 'approved'], 'Penyetujuan skor');
        });
    }

    public function reject(Score $score, User $chief, string $reason): void
    {
        DB::transaction(function () use ($score, $chief, $reason) {
            $score->status = ScoreStatus::Rejected;
            $score->save();

            $score->approvals()->create([
                'run_id' => $score->run_id,
                'decision' => 'rejected',
                'reason' => $reason,
                'decided_by' => $chief->id,
                'decided_at' => now(),
            ]);

            $score->writeAudit('updated', null, ['status' => 'rejected'], $reason);
        });
    }

    public function reopen(Score $score, User $chief, string $reason): void
    {
        DB::transaction(function () use ($score, $chief, $reason) {
            $score->status = ScoreStatus::Reopened;
            $score->reopened_at = now();
            $score->reopened_by = $chief->id;
            $score->reopen_reason = $reason;
            $score->save();

            $score->approvals()->create([
                'run_id' => $score->run_id,
                'decision' => 'reopened',
                'reason' => $reason,
                'decided_by' => $chief->id,
                'decided_at' => now(),
            ]);

            $score->writeAudit('updated', null, ['status' => 'reopened'], $reason);
        });
    }
}
