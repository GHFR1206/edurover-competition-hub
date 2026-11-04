<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'uuid', 'category_id', 'team_id', 'run_id', 'type', 'award',
        'qr_token', 'pdf_path', 'issued_at', 'issued_by',
    ];

    protected $auditAttributes = ['category_id', 'team_id', 'type', 'award', 'qr_token', 'issued_at', 'issued_by'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    /** @return BelongsTo<Category, Certificate> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Team, Certificate> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
