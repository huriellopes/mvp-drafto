<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostCollectionVisibilityEnum;
use App\Models\PostCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PostCollection>
 */
class PostCollectionFactory extends Factory
{
    protected $model = PostCollection::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(fake()->numberBetween(1, 3), true);

        return [
            'user_id' => User::factory()->writer(),
            'name' => Str::title($name),
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'description' => fake()->optional()->sentence(),
            'visibility' => PostCollectionVisibilityEnum::PRIVATE,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PostCollectionVisibilityEnum::PUBLIC,
        ]);
    }
}
