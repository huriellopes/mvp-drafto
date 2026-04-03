<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostView>
 */
class PostViewFactory extends Factory
{
    protected $model = PostView::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->published(),
            'user_id' => null,
            'session_id' => Str::uuid()->toString(),
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
            'viewed_at' => now()->subMinutes(fake()->numberBetween(1, 10000)),
        ];
    }

    public function byUser(?User $user = null): static
    {
        return $this->state(function () use ($user): array {
            $user ??= User::factory()->active()->create();

            return [
                'user_id' => $user->id,
                'session_id' => null,
            ];
        });
    }

    public function anonymous(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'session_id' => Str::uuid()->toString(),
        ]);
    }

    public function forPost(Post $post): static
    {
        return $this->state(fn (): array => [
            'post_id' => $post->id,
        ]);
    }
}
