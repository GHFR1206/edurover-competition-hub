<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\EvidenceFileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvidenceFile extends Model
{
    /** @use HasFactory<EvidenceFileFactory> */
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'run_id', 'score_id', 'score_line_id', 'uploaded_by', 'disk',
        'path', 'original_name', 'mime_type', 'size_bytes', 'kind', 'caption',
    ];

    protected $auditAttributes = ['run_id', 'score_id', 'score_line_id', 'kind', 'caption'];

    /** @return BelongsTo<Run, EvidenceFile> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /** @return BelongsTo<Score, EvidenceFile> */
    public function score(): BelongsTo
    {
        return $this->belongsTo(Score::class);
    }

    /** @return BelongsTo<ScoreLine, EvidenceFile> */
    public function scoreLine(): BelongsTo
    {
        return $this->belongsTo(ScoreLine::class);
    }
}
