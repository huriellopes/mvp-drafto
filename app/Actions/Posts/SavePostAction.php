<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class SavePostAction
{
    /**
     * Executes the post saving logic.
     * Handles both creation and update.
     */
    public function exec(User $user, SavePostData $dto, ?Post $post = null): Post
    {
        $data = $dto->toArray();

        if ($post) {
            $this->handleCoverImageCleanup($post, $dto->cover_image_path);

            $post->update($data);
            $this->updateSEO($post, $dto);

            return $post->fresh();
        }

        $post = $user->posts()->create(array_merge($data, [
            'status' => PostStatusEnum::DRAFT,
        ]));

        $this->updateSEO($post, $dto);

        return $post;
    }

    /**
     * Updates SEO metadata for the post.
     */
    private function updateSEO(Post $post, SavePostData $dto): void
    {
        if (!$dto->seo_enabled) {
            return;
        }

        $post->seo->update([
            'title' => $dto->seo_title ?: $dto->title,
            'description' => $dto->seo_description ?: $dto->excerpt,
        ]);
    }

    /**
     * Cleans up the old cover image if a new one is provided or if it's being removed.
     */
    private function handleCoverImageCleanup(Post $post, ?string $newPath): void
    {
        if ($newPath && $post->cover_image_path && $post->cover_image_path !== $newPath) {
            Storage::disk('public')->delete($post->cover_image_path);
        }
    }
}
