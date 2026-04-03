<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ThemePlatformEnum;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $username = fake()->unique()->userName() . fake()->unique()->numberBetween(10, 9999);

        return [
            'user_id' => User::factory(),
            'username' => Str::of($username)->lower()->replace('.', '_')->value(),
            'bio' => fake()->optional()->paragraph(),
            'avatar_path' => null,
            'cover_path' => null,
            'website_url' => fake()->optional()->url(),
            'location' => fake()->optional()->city(),
            'theme_mode' => fake()->randomElement(ThemePlatformEnum::cases()),
            'primary_color' => fake()->optional()->hexColor(),
            'accent_color' => fake()->optional()->hexColor(),
            'show_email_publicly' => false,
        ];
    }

    public function light(): static
    {
        return $this->state(fn (): array => [
            'theme_mode' => ThemePlatformEnum::LIGHT,
        ]);
    }

    public function dark(): static
    {
        return $this->state(fn (): array => [
            'theme_mode' => ThemePlatformEnum::DARK,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'theme_mode' => ThemePlatformEnum::SYSTEM,
        ]);
    }

    public function publicEmail(): static
    {
        return $this->state(fn (): array => [
            'show_email_publicly' => true,
        ]);
    }
}
