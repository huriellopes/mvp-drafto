<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommentStatusEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->published(),
            'user_id' => User::factory()->active(),
            'parent_id' => null,
            'content' => fake()->paragraph(),
            'status' => CommentStatusEnum::VISIBLE,
        ];
    }

    public function visible(): static
    {
        return $this->state(fn (): array => [
            'status' => CommentStatusEnum::VISIBLE,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatusEnum::HIDDEN,
        ]);
    }

    public function forPost(Post $post): static
    {
        return $this->state(fn (): array => [
            'post_id' => $post->id,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
        ]);
    }

    public function replyTo(Comment $comment): static
    {
        return $this->state(fn (): array => [
            'post_id' => $comment->post_id,
            'parent_id' => $comment->id,
        ]);
    }
}
