<?php

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\User;
use App\Enums\PostStatusEnum;
use Illuminate\Support\Facades\Storage;

final class SavePostAction
{
    public function exec(User $user, array $data, ?Post $post = null): Post
    {
        if ($post && empty($data['cover_image_path'])) {
            unset($data['cover_image_path']);
        }

        if ($post && !empty($data['cover_image_path'])) {
            if ($post->cover_image_path && Storage::disk('public')->exists($post->cover_image_path)) {
                Storage::disk('public')->delete($post->cover_image_path);
            }
        }

        if ($post) {
            $post->update($data);
            return $post;
        }

        return $user->posts()->create(array_merge($data, [
            'status' => PostStatusEnum::DRAFT,
        ]));
    }
}
