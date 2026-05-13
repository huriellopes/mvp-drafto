<?php

declare(strict_types=1);

namespace App\Events\Posts;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PostSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Post $post,
        public readonly array $seoData = [],
        public readonly ?string $oldImagePath = null,
    ) {}
}
