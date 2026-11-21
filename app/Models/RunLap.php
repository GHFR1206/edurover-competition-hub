<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\RunLapFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One lap split reported by an arena timing device.
 *
 * Supporting record only: the official run result lives on the run
 * (`official_time_ms`), and this table keeps the per-lap detail used for
 * tie-breaks, replay and the berita acara.
 */
class RunLap extends Model
{
    /** @use HasFactory<RunLapFactory> */
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'run_id', 'lap_no', 'lap_ms', 'cumulative_ms',
        'recorded_at', 'source', 'device_id',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'lap_no' => 'integer',
            'lap_ms' => 'integer',
            'cumulative_ms' => 'integer',
        ];
    }

    /** @return BelongsTo<Run, RunLap> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }
}
