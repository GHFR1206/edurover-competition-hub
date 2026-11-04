<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use App\Enums\Level;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'event_id', 'level', 'name', 'persona', 'description',
        'status', 'min_team_members', 'max_team_members', 'settings',
    ];

    protected $auditAttributes = ['level', 'name', 'status', 'min_team_members', 'max_team_members', 'settings'];

    protected function casts(): array
    {
        return [
            'level' => Level::class,
            'status' => CategoryStatus::class,
            'settings' => 'array',
        ];
    }

    /** @return BelongsTo<Event, Category> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return HasMany<Rulebook> */
    public function rulebooks(): HasMany
    {
        return $this->hasMany(Rulebook::class);
    }

    /** @return HasOne<Rulebook> */
    public function activeRulebook(): HasOne
    {
        return $this->hasOne(Rulebook::class)->latestOfMany('version');
    }

    /** @return HasOne<CategoryPublication> */
    public function publication(): HasOne
    {
        return $this->hasOne(CategoryPublication::class);
    }

    /** @return HasMany<Challenge> */
    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    /** @return HasMany<CapabilityTag> */
    public function capabilityTags(): HasMany
    {
        return $this->hasMany(CapabilityTag::class)->orderBy('position');
    }

    /** @return HasMany<Team> */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /** @return HasMany<Run> */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    public function isPublished(): bool
    {
        return $this->status === CategoryStatus::Published;
    }
}
