<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostCollectionVisibilityEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable(
    'user_id',
    'name',
    'slug',
    'description',
    'visibility',
)]
class PostCollection extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_collection_post')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'visibility' => PostCollectionVisibilityEnum::class,
        ];
    }
}
