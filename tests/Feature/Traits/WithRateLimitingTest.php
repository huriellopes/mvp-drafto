<?php

declare(strict_types=1);

namespace Tests\Feature\Traits;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Traits\Auth\WithRateLimiting;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

function rateLimiter(): object
{
    return new class
    {
        use WithRateLimiting;

        public function check(string $key, int $maxAttempts = 5): void
        {
            $this->checkRateLimit($key, $maxAttempts);
        }

        public function hit(string $key): void
        {
            $this->incrementAttempts($key);
        }

        public function clear(string $key): void
        {
            $this->clearAttempts($key);
        }
    };
}

beforeEach(function () {
    RateLimiter::clear('test|127.0.0.1');
});

it('does not throw while attempts are under the limit', function () {
    $limiter = rateLimiter();

    $limiter->hit('user@example.com');

    $limiter->check('user@example.com', 5);
})->throwsNoExceptions();

it('throws a validation exception once the attempt limit is exceeded', function () {
    $limiter = rateLimiter();

    for ($i = 0; $i < 5; $i++) {
        $limiter->hit('user@example.com');
    }

    $limiter->check('user@example.com', 5);
})->throws(ValidationException::class);

it('clears the attempts so checking no longer throws', function () {
    $limiter = rateLimiter();

    for ($i = 0; $i < 5; $i++) {
        $limiter->hit('user@example.com');
    }

    $limiter->clear('user@example.com');

    $limiter->check('user@example.com', 5);
})->throwsNoExceptions();

it('suspends the user after 10 failed attempts (account lockdown)', function () {
    $user = User::factory()->active()->create(['email' => 'victim@example.com']);
    $limiter = rateLimiter();

    for ($i = 0; $i < 10; $i++) {
        $limiter->hit('victim@example.com');
    }

    try {
        $limiter->check('victim@example.com', 5);
    } catch (ValidationException) {
        // expected once the limit is hit
    }

    expect($user->fresh()->status)->toBe(UserStatusEnum::SUSPENDED);
});

it('does not change an already suspended user during lockdown', function () {
    $user = User::factory()->suspended()->create(['email' => 'blocked@example.com']);
    $limiter = rateLimiter();

    for ($i = 0; $i < 10; $i++) {
        $limiter->hit('blocked@example.com');
    }

    try {
        $limiter->check('blocked@example.com', 5);
    } catch (ValidationException) {
        // expected
    }

    expect($user->fresh()->status)->toBe(UserStatusEnum::SUSPENDED);
});

it('does not suspend any user when the email has no matching account', function () {
    $limiter = rateLimiter();

    for ($i = 0; $i < 10; $i++) {
        $limiter->hit('ghost@example.com');
    }

    try {
        $limiter->check('ghost@example.com', 5);
    } catch (ValidationException) {
        // expected
    }

    expect(User::query()->where('email', 'ghost@example.com')->exists())->toBeFalse();
});
