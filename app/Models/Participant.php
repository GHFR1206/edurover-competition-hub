<?php

namespace App\Models;

use App\Enums\Level;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\ParticipantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    /** @use HasFactory<ParticipantFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'team_id', 'name', 'nisn', 'nisn_hash', 'level',
        'grade', 'role', 'birth_date', 'gender',
    ];

    /** NISN must never appear in audit snapshots or public payloads. */
    protected $auditAttributes = ['name', 'level', 'grade', 'role'];

    protected $hidden = ['nisn', 'nisn_hash'];

    protected function casts(): array
    {
        return [
            'level' => Level::class,
            'nisn' => 'encrypted',
            'birth_date' => 'date',
        ];
    }

    /** @return BelongsTo<Team, Participant> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** Blind index for duplicate checks without decrypting. */
    public static function hashNisn(?string $nisn): ?string
    {
        if ($nisn === null || $nisn === '') {
            return null;
        }

        return hash_hmac('sha256', $nisn, config('app.key'));
    }

    public static function maskNisn(?string $plain): ?string
    {
        if (! $plain) {
            return null;
        }

        return str_repeat('*', max(0, strlen($plain) - 4)).substr($plain, -4);
    }
}
