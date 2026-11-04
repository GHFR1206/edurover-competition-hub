<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'event_id', 'category_id', 'team_id', 'coach_id', 'status',
        'consent_parent', 'consent_media', 'consent_at', 'submitted_at',
        'verified_at', 'verified_by', 'rejection_reason', 'documents', 'notes',
    ];

    protected $auditAttributes = [
        'status', 'consent_parent', 'consent_media',
        'verified_at', 'verified_by', 'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'consent_parent' => 'boolean',
            'consent_media' => 'boolean',
            'consent_at' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'documents' => 'array',
        ];
    }

    /** @return BelongsTo<Event, Registration> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Category, Registration> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Team, Registration> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return BelongsTo<Coach, Registration> */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
