<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Database\Factories\CapabilityTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapabilityTag extends Model
{
    /** @use HasFactory<CapabilityTagFactory> */
    use Auditable, HasFactory, HasUuid;

    protected $fillable = ['uuid', 'category_id', 'code', 'name', 'description', 'position'];

    protected $auditAttributes = ['code', 'name', 'position'];

    /** @return BelongsTo<Category, CapabilityTag> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
