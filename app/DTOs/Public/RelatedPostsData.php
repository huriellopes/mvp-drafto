<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use App\Models\Post;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class RelatedPostsData extends Data
{
    /**
     * @param  Collection<Post>  $posts
     */
    public function __construct(
        public Collection $posts,
    ) {}
}
