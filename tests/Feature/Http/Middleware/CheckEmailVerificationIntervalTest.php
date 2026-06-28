<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Middleware\CheckEmailVerificationInterval;
use App\Models\User;
use Illuminate\Http\Request;

function runVerificationIntervalMiddleware(?User $user)
{
    $request = Request::create('/dashboard');
    $request->setUserResolver(fn () => $user);

    return (new CheckEmailVerificationInterval())->handle(
        $request,
        fn () => response('ok'),
    );
}

it('redirects to the verification notice when the grace period expired', function () {
    $user = User::factory()->unverified()->create([
        'created_at' => now()->subDays(30),
    ]);

    $response = runVerificationIntervalMiddleware($user);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain(route('verification.notice'));
});

it('allows verified users through', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(30)]);

    $response = runVerificationIntervalMiddleware($user);

    expect($response->getContent())->toBe('ok');
});

it('allows unverified users still within the grace period', function () {
    $user = User::factory()->unverified()->create(['created_at' => now()->subDay()]);

    $response = runVerificationIntervalMiddleware($user);

    expect($response->getContent())->toBe('ok');
});

it('allows guests through', function () {
    $response = runVerificationIntervalMiddleware(null);

    expect($response->getContent())->toBe('ok');
});
