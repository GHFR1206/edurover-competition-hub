<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\HeatSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HeatSlot extends Model
{
    /** @use HasFactory<HeatSlotFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'arena_id', 'category_id', 'slot_no', 'label',
        'starts_at', 'ends_at', 'status',
    ];

    protected $auditAttributes = ['arena_id', 'slot_no', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Arena, HeatSlot> */
    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    /** @return BelongsTo<Category, HeatSlot> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Run> */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }
}
