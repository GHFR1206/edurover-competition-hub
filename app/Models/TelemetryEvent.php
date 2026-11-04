<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\TelemetryEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Supporting telemetry from EduRover robots. Never contributes to official scores.
 * Ingest is gated behind the `FEATURE_TELEMETRY` flag (default OFF).
 */
class TelemetryEvent extends Model
{
    /** @use HasFactory<TelemetryEventFactory> */
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'run_id', 'device_id', 'event_type', 'payload',
        'occurred_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Run, TelemetryEvent> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }
}
