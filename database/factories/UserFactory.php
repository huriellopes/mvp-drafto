<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => RoleEnum::READER,
            'status' => UserStatusEnum::ACTIVE,
            'last_login_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatusEnum::PENDING,
            'last_login_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatusEnum::SUSPENDED,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatusEnum::BLOCKED,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatusEnum::ACTIVE,
        ]);
    }

    public function reader(): static
    {
        return $this->state(fn (): array => [
            'role' => RoleEnum::READER,
        ]);
    }

    public function writer(): static
    {
        return $this->state(fn (): array => [
            'role' => RoleEnum::WRITER,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => [
            'role' => RoleEnum::SUPER_ADMIN,
        ]);
    }

    public function withProfile(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->profile()->exists()) {
                return;
            }

            $user->profile()->create(
                Profile::factory()->make([
                    'user_id' => $user->id,
                ])->toArray(),
            );
        });
    }
}
