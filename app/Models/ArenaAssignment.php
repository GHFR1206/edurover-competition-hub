<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\ArenaAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArenaAssignment extends Model
{
    /** @use HasFactory<ArenaAssignmentFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = ['uuid', 'arena_id', 'user_id', 'role', 'starts_at', 'ends_at', 'is_active'];

    protected $auditAttributes = ['arena_id', 'user_id', 'role', 'is_active'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Arena, ArenaAssignment> */
    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    /** @return BelongsTo<User, ArenaAssignment> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
