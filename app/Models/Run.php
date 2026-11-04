<?php

namespace App\Models;

use App\Enums\RunStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\RunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Run extends Model
{
    /** @use HasFactory<RunFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'event_id', 'category_id', 'arena_id', 'team_id', 'heat_slot_id',
        'attempt_no', 'status', 'scheduled_at', 'called_at', 'started_at',
        'finished_at', 'duration_seconds', 'notes', 'outcome_reason', 'operator_id',
    ];

    protected $auditAttributes = [
        'status', 'attempt_no', 'started_at', 'finished_at',
        'duration_seconds', 'outcome_reason', 'operator_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => RunStatus::class,
            'scheduled_at' => 'datetime',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Event, Run> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Category, Run> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Arena, Run> */
    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    /** @return BelongsTo<Team, Run> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return BelongsTo<HeatSlot, Run> */
    public function heatSlot(): BelongsTo
    {
        return $this->belongsTo(HeatSlot::class);
    }

    /** @return HasMany<Score> */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /** @return HasMany<EvidenceFile> */
    public function evidenceFiles(): HasMany
    {
        return $this->hasMany(EvidenceFile::class);
    }
}
