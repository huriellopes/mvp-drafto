<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(fake()->numberBetween(3, 8));
        $content = fake()->paragraphs(fake()->numberBetween(8, 16), true);

        return [
            'user_id' => User::factory()->writer(),
            'category_id' => PostCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
            'excerpt' => fake()->optional()->text(180),
            'content' => $content,
            'type' => fake()->randomElement([
                PostTypeEnum::POST,
                PostTypeEnum::ARTICLE,
            ]),
            'cover_image_path' => null,
            'status' => PostStatusEnum::DRAFT,
            'visibility' => PostVisibilityEnum::PUBLIC,
            'published_at' => null,
            'featured_at' => null,
            'comments_enabled' => true,
            'reading_time' => max(1, (int) ceil(str_word_count(strip_tags($content)) / 200)),
            'views_count' => 0,
            'likes_count' => 0,
            'comments_count' => 0,
        ];
    }

    public function post(): static
    {
        return $this->state(fn (): array => [
            'type' => PostTypeEnum::POST,
        ]);
    }

    public function article(): static
    {
        return $this->state(fn (): array => [
            'type' => PostTypeEnum::ARTICLE,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatusEnum::DRAFT,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatusEnum::PUBLISHED,
            'published_at' => now()->subMinutes(fake()->numberBetween(5, 5000)),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatusEnum::ARCHIVED,
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PostVisibilityEnum::PUBLIC,
        ]);
    }

    public function unlisted(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PostVisibilityEnum::UNLISTED,
        ]);
    }

    public function followersOnly(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PostVisibilityEnum::FOLLOWERS_ONLY,
        ]);
    }

    public function withoutComments(): static
    {
        return $this->state(fn (): array => [
            'comments_enabled' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'featured_at' => now(),
        ]);
    }

    public function forAuthor(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
        ]);
    }

    public function uncategorized(): static
    {
        return $this->state(fn (): array => [
            'category_id' => null,
        ]);
    }

    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Post $post) use ($count): void {
            $tags = Tag::factory()->count($count)->create();

            $post->tags()->syncWithoutDetaching($tags->modelKeys());
        });
    }
}
