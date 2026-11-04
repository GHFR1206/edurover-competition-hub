<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'school_id', 'category_id', 'name', 'slug', 'short_name', 'status',
    ];

    protected $auditAttributes = ['name', 'short_name', 'status', 'school_id', 'category_id'];

    protected function casts(): array
    {
        return ['status' => RegistrationStatus::class];
    }

    /** @return BelongsTo<School, Team> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<Category, Team> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Participant> */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /** @return HasMany<Run> */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    /** @return HasMany<Certificate> */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** Public label never exposes personal data of children. */
    public function publicLabel(): string
    {
        return "{$this->name} — {$this->school?->name}";
    }
}
