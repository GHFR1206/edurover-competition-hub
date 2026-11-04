<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\AppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appeal extends Model
{
    /** @use HasFactory<AppealFactory> */
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'run_id', 'team_id', 'submitted_by', 'reason',
        'status', 'resolution', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    /** @return BelongsTo<Run, Appeal> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /** @return BelongsTo<Team, Appeal> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
