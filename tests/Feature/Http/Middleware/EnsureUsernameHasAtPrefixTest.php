<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Middleware\EnsureUsernameHasAtPrefix;
use App\Models\User;
use Illuminate\Http\Request;

it('passes paths that already start with @ straight through', function () {
    $middleware = new EnsureUsernameHasAtPrefix();

    $response = $middleware->handle(
        Request::create('/@someuser'),
        fn () => response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});

it('301 redirects a bare existing username to the @ prefixed url', function () {
    $user = User::factory()->withProfile()->create();
    $username = $user->profile->username;

    $middleware = new EnsureUsernameHasAtPrefix();

    $response = $middleware->handle(
        Request::create('/' . $username),
        fn () => response('ok'),
    );

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toEndWith('/@' . $username);
});

it('passes through when the first segment is not a known profile', function () {
    $middleware = new EnsureUsernameHasAtPrefix();

    $response = $middleware->handle(
        Request::create('/unknown-segment'),
        fn () => response('ok'),
    );

    expect($response->getContent())->toBe('ok');
});
