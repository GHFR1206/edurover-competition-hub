<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\ArenaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arena extends Model
{
    /** @use HasFactory<ArenaFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'event_id', 'api_user_id', 'token_issued_at', 'name', 'code',
        'location', 'status', 'capacity_per_slot', 'slot_minutes', 'is_active', 'settings',
    ];

    protected $auditAttributes = ['name', 'code', 'location', 'status', 'is_active', 'settings'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
            'token_issued_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Event, Arena> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<User, Arena> */
    public function apiUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'api_user_id');
    }

    /** @return HasMany<ArenaAssignment> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ArenaAssignment::class);
    }

    /** @return HasMany<HeatSlot> */
    public function heatSlots(): HasMany
    {
        return $this->hasMany(HeatSlot::class);
    }

    /** @return HasMany<Run> */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    /** @return BelongsToMany<User> */
    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'arena_assignments')
            ->wherePivot('role', 'operator')
            ->withTimestamps();
    }

    /** @return BelongsToMany<User> */
    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'arena_assignments')
            ->wherePivot('role', 'judge')
            ->withTimestamps();
    }
}
