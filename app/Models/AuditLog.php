<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only. The application never updates or deletes rows in this table.
 */
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory, HasUuid;

    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid', 'user_id', 'actor_name', 'action', 'auditable_type',
        'auditable_id', 'before', 'after', 'reason', 'ip_address',
        'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, AuditLog> */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, AuditLog> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
