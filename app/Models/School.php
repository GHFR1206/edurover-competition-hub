<?php

namespace App\Models;

use App\Enums\Level;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'npsn', 'level', 'address', 'city', 'province',
        'contact_name', 'contact_phone', 'contact_email',
    ];

    protected $auditAttributes = ['name', 'npsn', 'level', 'city', 'province'];

    protected function casts(): array
    {
        return ['level' => Level::class];
    }

    /** @return HasMany<Team> */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /** @return HasMany<Coach> */
    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class);
    }
}
