<?php

declare(strict_types=1);

namespace Tests\Feature\Traits;

use App\Models\Profile;
use App\Traits\GeneratesUsername;

function usernameGenerator(): object
{
    return new class
    {
        use GeneratesUsername;

        public function make(string $name): string
        {
            return $this->generateUniqueUsername($name);
        }
    };
}

it('builds a slugged username with a random 4-char suffix', function () {
    $username = usernameGenerator()->make('John Doe');

    expect($username)->toMatch('/^john-doe-[a-z0-9]{4}$/');
});

it('strips @ from the provided name before slugging', function () {
    $username = usernameGenerator()->make('@handle');

    expect($username)->toStartWith('handle-')
        ->and($username)->not->toContain('@');
});

it('produces a username that is not already taken', function () {
    $generator = usernameGenerator();

    $first = $generator->make('Unique Name');
    Profile::factory()->create(['username' => $first]);

    $second = $generator->make('Unique Name');

    expect($second)->not->toBe($first)
        ->and(Profile::query()->where('username', $second)->exists())->toBeFalse();
});
