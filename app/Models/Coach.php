<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\CoachFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coach extends Model
{
    /** @use HasFactory<CoachFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = ['uuid', 'user_id', 'school_id', 'name', 'phone', 'email'];

    protected $auditAttributes = ['name', 'phone', 'email', 'school_id'];

    /** @return BelongsTo<School, Coach> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<User, Coach> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
