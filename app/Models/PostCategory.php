<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'slug',
    'description',
])]
class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory, HasSlug;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    /**
     * Scope to get global categories (Admin) or categories from a specific user.
     */
    public function scopeForUser($query, ?int $userId = null)
    {
        return $query->whereNull('user_id')
            ->when($userId, fn ($q) => $q->orWhere('user_id', $userId));
    }
}
