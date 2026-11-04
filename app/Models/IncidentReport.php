<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\IncidentReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReport extends Model
{
    /** @use HasFactory<IncidentReportFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'event_id', 'arena_id', 'run_id', 'reported_by', 'severity',
        'category', 'title', 'description', 'status', 'resolution', 'resolved_at',
    ];

    protected $auditAttributes = ['severity', 'category', 'title', 'status', 'resolution'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    /** @return BelongsTo<Event, IncidentReport> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Arena, IncidentReport> */
    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    /** @return BelongsTo<Run, IncidentReport> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }
}
