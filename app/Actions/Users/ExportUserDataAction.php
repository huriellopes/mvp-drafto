<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

/**
 * @return array<string, mixed>
 */
final class ExportUserDataAction
{
    /**
     * @return array<string, mixed>
     */
    public function exec(User $user): array
    {
        $user->loadMissing(['profile', 'posts', 'comments', 'collections']);

        return [
            'exported_at' => now()->toIso8601String(),
            'account' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'status',
                'email_verified_at',
                'wants_reengagement_emails',
                'wants_product_updates',
                'created_at',
            ]),
            'profile' => $user->profile?->only([
                'name',
                'username',
                'email',
                'bio',
                'website_url',
                'location',
                'visibility',
                'created_at',
            ]),
            'posts' => $user->posts->map(fn ($post) => $post->only([
                'id',
                'title',
                'slug',
                'status',
                'content',
                'published_at',
                'created_at',
            ]))->values()->all(),
            'comments' => $user->comments->map(fn ($comment) => $comment->only([
                'id',
                'post_id',
                'content',
                'status',
                'created_at',
            ]))->values()->all(),
            'collections' => $user->collections->map(fn ($collection) => $collection->only([
                'id',
                'name',
                'slug',
                'description',
                'created_at',
            ]))->values()->all(),
        ];
    }
}
