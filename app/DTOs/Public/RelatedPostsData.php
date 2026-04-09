<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use App\Models\Post;
use Illuminate\Support\Collection;

readonly class RelatedPostsData
{
    /**
     * @param  Collection<Post>  $posts
     */
    public function __construct(
        public Collection $posts,
    ) {}
}
