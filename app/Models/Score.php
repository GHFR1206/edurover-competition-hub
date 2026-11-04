<?php

namespace App\Models;

use App\Enums\ScoreStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\ScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Score extends Model
{
    /** @use HasFactory<ScoreFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'run_id', 'judge_id', 'version', 'status',
        'subtotal', 'penalty_total', 'total', 'ruleset_snapshot',
        'submitted_at', 'approved_at', 'approved_by', 'reopened_at',
        'reopened_by', 'reopen_reason', 'idempotency_key',
    ];

    protected $auditAttributes = [
        'status', 'version', 'subtotal', 'penalty_total', 'total',
        'submitted_at', 'approved_at', 'approved_by',
        'reopened_at', 'reopened_by', 'reopen_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScoreStatus::class,
            'ruleset_snapshot' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'reopened_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Run, Score> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /** @return BelongsTo<User, Score> */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    /** @return HasMany<ScoreLine> */
    public function lines(): HasMany
    {
        return $this->hasMany(ScoreLine::class);
    }

    /** @return HasMany<ResultApproval> */
    public function approvals(): HasMany
    {
        return $this->hasMany(ResultApproval::class);
    }
}
