<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\ScoreLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreLine extends Model
{
    /** @use HasFactory<ScoreLineFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'score_id', 'score_object_id', 'quantity', 'achieved',
        'points_per_unit_snapshot', 'computed_points', 'notes',
    ];

    protected $auditAttributes = [
        'score_object_id', 'quantity', 'achieved',
        'points_per_unit_snapshot', 'computed_points',
    ];

    protected function casts(): array
    {
        return [
            'achieved' => 'boolean',
            'quantity' => 'integer',
            'points_per_unit_snapshot' => 'integer',
            'computed_points' => 'integer',
        ];
    }

    /** @return BelongsTo<Score, ScoreLine> */
    public function score(): BelongsTo
    {
        return $this->belongsTo(Score::class);
    }

    /** @return BelongsTo<ScoreObject, ScoreLine> */
    public function scoreObject(): BelongsTo
    {
        return $this->belongsTo(ScoreObject::class);
    }
}
