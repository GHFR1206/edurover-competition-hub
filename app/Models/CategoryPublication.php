<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\CategoryPublicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryPublication extends Model
{
    /** @use HasFactory<CategoryPublicationFactory> */
    use HasFactory, HasUuid;

    protected $fillable = ['uuid', 'category_id', 'published_at', 'published_by', 'notes'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** @return BelongsTo<Category, CategoryPublication> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<User, CategoryPublication> */
    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
