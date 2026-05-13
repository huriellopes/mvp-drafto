<?php

declare(strict_types=1);

use App\Http\Middleware\CheckBanned;
use App\Models\User;
use Illuminate\Http\Request;

it('logs out and redirects banned user', function () {
    $user = User::factory()->create([
        'banned_until' => now()->addDays(5),
        'ban_reason' => 'Spamming',
    ]);

    $this->actingAs($user);

    $request = Request::create('/dashboard', 'GET');
    $middleware = new CheckBanned();

    $response = $middleware->handle($request, function () {
        return response('Success');
    });

    expect($response->isRedirect(route('login')))->toBeTrue()
        ->and(auth()->check())->toBeFalse();

    expect(session('errors')->getBag('default')->has('email'))->toBeTrue();
});

it('allows access to active users', function () {
    $user = User::factory()->create([
        'banned_until' => null,
    ]);

    $this->actingAs($user);

    $request = Request::create('/dashboard', 'GET');
    $middleware = new CheckBanned();

    $response = $middleware->handle($request, function () {
        return response('Success');
    });

    expect($response->getContent())->toBe('Success')
        ->and(auth()->check())->toBeTrue();
});
