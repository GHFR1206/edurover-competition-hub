<?php

namespace App\Models;

use App\Enums\ScoreObjectType;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\ScoreObjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreObject extends Model
{
    /** @use HasFactory<ScoreObjectFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'challenge_id', 'capability_tag_id', 'code', 'name', 'description',
        'type', 'points_per_unit', 'max_quantity', 'requires_evidence',
        'is_bonus', 'position', 'settings',
    ];

    protected $auditAttributes = [
        'code', 'name', 'type', 'points_per_unit', 'max_quantity',
        'requires_evidence', 'is_bonus', 'position', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => ScoreObjectType::class,
            'points_per_unit' => 'integer',
            'max_quantity' => 'integer',
            'requires_evidence' => 'boolean',
            'is_bonus' => 'boolean',
            'settings' => 'array',
        ];
    }

    /** @return BelongsTo<Challenge, ScoreObject> */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /** @return BelongsTo<CapabilityTag, ScoreObject> */
    public function capabilityTag(): BelongsTo
    {
        return $this->belongsTo(CapabilityTag::class);
    }
}
