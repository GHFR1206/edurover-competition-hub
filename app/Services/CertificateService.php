<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Issues simple participation/award certificates for a published category.
 * Only published categories can produce certificates, and each (category,
 * team, type) combination is issued at most once.
 */
class CertificateService
{
    public function __construct(private readonly StandingsService $standings) {}

    /**
     * Issue participation + achievement certificates for every team in a
     * published category. Returns the number of certificates created.
     */
    public function issueForCategory(Category $category, User $issuer): int
    {
        if (! $category->isPublished()) {
            throw new RuntimeException('Sertifikat hanya untuk kategori yang sudah dipublikasikan.');
        }

        $standings = $this->standings->forCategory($category);
        $created = 0;

        foreach ($standings as $row) {
            $team = $row['team'];

            $award = match ($row['rank']) {
                1 => 'Juara 1',
                2 => 'Juara 2',
                3 => 'Juara 3',
                default => null,
            };

            if (! Certificate::where('category_id', $category->id)->where('team_id', $team->id)->where('type', 'participation')->exists()) {
                Certificate::create([
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $category->id,
                    'team_id' => $team->id,
                    'run_id' => null,
                    'type' => 'participation',
                    'award' => null,
                    'qr_token' => (string) Str::uuid(),
                    'issued_at' => now(),
                    'issued_by' => $issuer->id,
                ]);
                $created++;
            }

            if ($award && ! Certificate::where('category_id', $category->id)->where('team_id', $team->id)->where('type', 'achievement')->exists()) {
                Certificate::create([
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $category->id,
                    'team_id' => $team->id,
                    'run_id' => null,
                    'type' => 'achievement',
                    'award' => $award,
                    'qr_token' => (string) Str::uuid(),
                    'issued_at' => now(),
                    'issued_by' => $issuer->id,
                ]);
                $created++;
            }
        }

        return $created;
    }
}
