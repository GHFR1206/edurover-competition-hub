<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 'venue',
        'starts_at', 'ends_at', 'status', 'created_by',
    ];

    protected $auditAttributes = ['name', 'slug', 'venue', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    /** @return HasMany<Category> */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /** @return HasMany<Arena> */
    public function arenas(): HasMany
    {
        return $this->hasMany(Arena::class);
    }

    /** @return HasMany<Run> */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    /**
     * Order events the way the public site wants them: running first, then
     * scheduled, then finished. Portable across MySQL and SQLite (avoids the
     * MySQL-only FIELD() function).
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeInDisplayOrder($query)
    {
        return $query->orderByRaw(
            'case status when ? then 0 when ? then 1 when ? then 2 else 3 end',
            ['running', 'scheduled', 'finished'],
        );
    }
}
