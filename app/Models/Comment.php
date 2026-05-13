<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommentStatusEnum;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

#[Fillable([
    'post_id',
    'user_id',
    'parent_id',
    'content',
    'status',
])]
class Comment extends Model implements Auditable
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, KeepsDeletedModels, \OwenIt\Auditing\Auditable;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::VISIBLE);
    }

    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with([
            'user.profile',
            'replies.user.profile',
            'replies.replies.user.profile',
        ])
            ->withCount('likedByUsers')
            ->when(auth()->check(), fn ($q) => $q->withExists(['likedByUsers as is_liked' => fn ($q) => $q->where('user_id', auth()->id())]));
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_likes', 'comment_id', 'user_id')
            ->withTimestamps();
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    protected function casts(): array
    {
        return [
            'status' => CommentStatusEnum::class,
        ];
    }
}
