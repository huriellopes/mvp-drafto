<?php

declare(strict_types=1);

use App\Actions\Auth\VerifyEmailAction;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->action = app(VerifyEmailAction::class);
});

function makeVerifyRequest(string $id, string $hash): Request
{
    $request = Request::create("/verify-email/{$id}/{$hash}", 'GET');

    $route = new Route('GET', '/verify-email/{id}/{hash}', []);
    $route->bind($request);
    $route->setParameter('id', $id);
    $route->setParameter('hash', $hash);

    $request->setRouteResolver(fn () => $route);

    return $request;
}

it('marks the email as verified and fires the Verified event', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();

    $request = makeVerifyRequest(
        (string) $user->id,
        sha1($user->getEmailForVerification()),
    );

    $result = $this->action->exec($request);

    expect($result)->toBeTrue()
        ->and($user->fresh()->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(Verified::class);
});

it('returns false when the hash does not match', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();

    $request = makeVerifyRequest((string) $user->id, 'invalid-hash');

    $result = $this->action->exec($request);

    expect($result)->toBeFalse()
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse();

    Event::assertNotDispatched(Verified::class);
});

it('returns true without re-firing the event when already verified', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->create(['email_verified_at' => now()]);

    $request = makeVerifyRequest(
        (string) $user->id,
        sha1($user->getEmailForVerification()),
    );

    $result = $this->action->exec($request);

    expect($result)->toBeTrue();

    Event::assertNotDispatched(Verified::class);
});
