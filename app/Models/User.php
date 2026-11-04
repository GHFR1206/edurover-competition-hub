<?php

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Only the seven allowed roles may enter the staff panel, and only the
     * staff subset. Judges and coaches use their own Blade/Livewire pages.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->isStaff();
    }

    /** @return HasOne<Coach> */
    public function coachProfile(): HasOne
    {
        return $this->hasOne(Coach::class);
    }

    /** @return HasMany<ArenaAssignment> */
    public function arenaAssignments(): HasMany
    {
        return $this->hasMany(ArenaAssignment::class);
    }

    /** @return HasMany<Score> */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'judge_id');
    }

    /** Convenience: does the user hold the given platform role? */
    public function isRole(RoleEnum $role): bool
    {
        return $this->hasRole($role->value);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(array_map(fn (RoleEnum $r) => $r->value, RoleEnum::staff()));
    }
}
