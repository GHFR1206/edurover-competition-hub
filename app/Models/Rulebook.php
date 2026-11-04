<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\RulebookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rulebook extends Model
{
    /** @use HasFactory<RulebookFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'category_id', 'version', 'title', 'summary',
        'aggregation', 'tie_break_order', 'penalties', 'retry_rules',
        'status', 'locked_at', 'locked_by', 'created_by',
    ];

    protected $auditAttributes = ['version', 'title', 'aggregation', 'tie_break_order', 'penalties', 'retry_rules', 'status'];

    protected function casts(): array
    {
        return [
            'aggregation' => 'array',
            'tie_break_order' => 'array',
            'penalties' => 'array',
            'retry_rules' => 'array',
            'locked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Category, Rulebook> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
