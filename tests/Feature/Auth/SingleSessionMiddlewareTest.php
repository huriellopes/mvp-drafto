<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\SingleSession;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/__single-session', fn () => 'ok')->middleware(['web', 'auth']);
    Route::get('/__single-session-guest', fn () => 'ok')->middleware('web');
});

it('issues a token in both the session and the cache on login', function () {
    $user = User::factory()->create();

    event(new Login('web', $user, false));

    expect(session(SingleSession::SESSION_KEY))->toBeString()
        ->toBe(Cache::get(SingleSession::cacheKey($user->id)));
});

it('keeps the session that holds the current token', function () {
    $user = User::factory()->create();
    Cache::forever(SingleSession::cacheKey($user->id), 'current');

    $this->actingAs($user)
        ->withSession([SingleSession::SESSION_KEY => 'current'])
        ->get('/__single-session')
        ->assertOk();
});

it('terminates a session superseded by a newer login, for any session driver', function () {
    $user = User::factory()->create();
    Cache::forever(SingleSession::cacheKey($user->id), 'newer-login');

    $this->actingAs($user)
        ->withSession([SingleSession::SESSION_KEY => 'older-login'])
        ->get('/__single-session')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('leaves sessions alone while no token was issued yet', function () {
    $user = User::factory()->create();
    Cache::forget(SingleSession::cacheKey($user->id));

    $this->actingAs($user)
        ->get('/__single-session')
        ->assertOk();
});

it('ignores guests', function () {
    Cache::forever(SingleSession::cacheKey(1), 'any');

    $this->get('/__single-session-guest')->assertOk();
});

it('returns null when the cached token is not a string', function () {
    Cache::forever(SingleSession::cacheKey(5), ['corrupted']);

    expect(SingleSession::current(5))->toBeNull();
});
