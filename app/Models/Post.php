<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Modules\GenerateShortLinkAction;
use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Services\Post\ReadingTimeCalculator;
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
use OwenIt\Auditing\Contracts\Auditable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

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
    'seo_enabled',
    'reading_time',
    'views_count',
    'likes_count',
    'comments_count',
])]
class Post extends Model implements Auditable, Sitemapable
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, KeepsDeletedModels, \OwenIt\Auditing\Auditable;

    use HasSEO, HasSlug;

    protected array $auditExclude = [
        'views_count',
        'likes_count',
        'comments_count',
    ];

    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('posts.show', $this->slug))
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8);
    }

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

    public function shortLinks(): MorphMany
    {
        return $this->morphMany(ShortLink::class, 'shortable');
    }

    /**
     * Sênior: Retorna a URL de compartilhamento, encurtada se o módulo estiver ativo.
     */
    public function getShareUrl(): string
    {
        return app(GenerateShortLinkAction::class)->exec(
            user: auth()->user() ?? $this->author,
            shortable: $this,
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatusEnum::PUBLISHED);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', PostStatusEnum::SCHEDULED);
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

    public function isScheduled(): bool
    {
        return $this->status === PostStatusEnum::SCHEDULED;
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
            if (!$this->cover_image_path) {
                return;
            }

            if (str_starts_with($this->cover_image_path, 'http')) {
                return $this->cover_image_path;
            }

            return asset('storage/' . $this->cover_image_path);
        });
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if ($post->isDirty('content')) {
                $post->reading_time = ReadingTimeCalculator::calculate($post->content);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'type' => PostTypeEnum::class,
            'status' => PostStatusEnum::class,
            'visibility' => PostVisibilityEnum::class,
            'comments_enabled' => 'boolean',
            'seo_enabled' => 'boolean',
            'reading_time' => 'integer',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'published_at' => 'datetime',
            'featured_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
