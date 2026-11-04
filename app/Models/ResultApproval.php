<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\ResultApprovalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultApproval extends Model
{
    /** @use HasFactory<ResultApprovalFactory> */
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'score_id', 'run_id', 'decision', 'reason', 'decided_by', 'decided_at',
    ];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    /** @return BelongsTo<Score, ResultApproval> */
    public function score(): BelongsTo
    {
        return $this->belongsTo(Score::class);
    }

    /** @return BelongsTo<Run, ResultApproval> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /** @return BelongsTo<User, ResultApproval> */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
