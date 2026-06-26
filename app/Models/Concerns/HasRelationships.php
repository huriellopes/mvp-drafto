<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\PostStatusEnum;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\MagicLoginToken;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\PostView;
use App\Models\Profile;
use App\Models\Report;
use App\Models\SavedPost;
use App\Models\ShortLink;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Relacionamentos do usuário (exceto seguidores e módulos, que têm concerns próprios).
 */
trait HasRelationships
{
    /** @return HasOne<Profile, $this> */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return HasMany<MagicLoginToken, $this> */
    public function magicLoginTokens(): HasMany
    {
        return $this->hasMany(MagicLoginToken::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', PostStatusEnum::PUBLISHED);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'post_likes',
            'user_id',
            'post_id',
        )->withTimestamps();
    }

    public function likedComments(): BelongsToMany
    {
        return $this->belongsToMany(
            Comment::class,
            'comment_likes',
            'user_id',
            'comment_id',
        )->withTimestamps();
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Coleções de obras criadas pelo escritor (séries, ensinamentos...).
     *
     * @return HasMany<PostCollection, $this>
     */
    public function postCollections(): HasMany
    {
        return $this->hasMany(PostCollection::class);
    }

    public function savedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'saved_posts')
            ->using(SavedPost::class)
            ->withPivot('collection_id', 'id')
            ->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reviewedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    public function postViews(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function shortLinks(): MorphMany
    {
        return $this->morphMany(ShortLink::class, 'shortable');
    }
}
