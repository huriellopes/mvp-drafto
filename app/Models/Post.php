<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Traits\HasSlug;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

#[Fillable([
    'user_id',
    'category_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'type',
    'cover_image_path',
    'status',
    'visibility',
    'published_at',
    'featured_at',
    'comments_enabled',
    'reading_time',
    'views_count',
    'likes_count',
    'comments_count',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, KeepsDeletedModels;
    use HasSlug;

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')
            ->withTimestamps();
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_posts', 'post_id', 'user_id')
            ->withTimestamps();
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatusEnum::PUBLISHED);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', PostVisibilityEnum::PUBLIC);
    }

    public function scopeArticles(Builder $query): Builder
    {
        return $query->where('type', PostTypeEnum::ARTICLE);
    }

    public function scopeRegularPosts(Builder $query): Builder
    {
        return $query->where('type', PostTypeEnum::POST);
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatusEnum::PUBLISHED;
    }

    public function isArticle(): bool
    {
        return $this->type === PostTypeEnum::ARTICLE;
    }

    public function isRegularPost(): bool
    {
        return $this->type === PostTypeEnum::POST;
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->cover_image_path) return null;

            if (filter_var($this->cover_image_path, FILTER_VALIDATE_URL)) {
                return $this->cover_image_path;
            }

            return Storage::disk('public')->url($this->cover_image_path);
        });
    }

    protected function casts(): array
    {
        return [
            'type' => PostTypeEnum::class,
            'status' => PostStatusEnum::class,
            'visibility' => PostVisibilityEnum::class,
            'comments_enabled' => 'boolean',
            'reading_time' => 'integer',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'published_at' => 'datetime',
            'featured_at' => 'datetime',
        ];
    }
}
