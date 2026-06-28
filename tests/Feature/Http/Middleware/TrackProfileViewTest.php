<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Middleware\TrackProfileView;
use App\Jobs\ProcessProfileViewJob;
use App\Models\User;
use App\Support\CookieConsent;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Queue;

function consentCookie(): array
{
    return [CookieConsent::COOKIE => rawurlencode(json_encode(['analytics' => true]))];
}

function terminateTrackProfileView(string $username, int $status, array $cookies = [], ?User $actingUser = null): void
{
    $request = Request::create('/@' . $username, 'GET', cookies: $cookies);
    $request->setRouteResolver(function () use ($request, $username) {
        return tap(new Route('GET', '/@{username}', []), function ($route) use ($request, $username) {
            $route->name('profile.show');
            $route->bind($request);
            $route->setParameter('username', $username);
        });
    });

    if ($actingUser) {
        $request->setUserResolver(fn () => $actingUser);
        auth()->setUser($actingUser);
    }

    $response = response('ok', $status);

    (new TrackProfileView())->terminate($request, $response);
}

it('dispatches a profile view job for a visitor with analytics consent', function () {
    Queue::fake();

    $owner = User::factory()->withProfile()->create();

    terminateTrackProfileView($owner->profile->username, 200, consentCookie());

    Queue::assertPushed(ProcessProfileViewJob::class);
});

it('does not dispatch without analytics consent', function () {
    Queue::fake();

    $owner = User::factory()->withProfile()->create();

    terminateTrackProfileView($owner->profile->username, 200, []);

    Queue::assertNotPushed(ProcessProfileViewJob::class);
});

it('does not dispatch for non 200 responses', function () {
    Queue::fake();

    $owner = User::factory()->withProfile()->create();

    terminateTrackProfileView($owner->profile->username, 404, consentCookie());

    Queue::assertNotPushed(ProcessProfileViewJob::class);
});

it('does not dispatch when the owner views their own profile', function () {
    Queue::fake();

    $owner = User::factory()->withProfile()->create();

    terminateTrackProfileView($owner->profile->username, 200, consentCookie(), $owner);

    Queue::assertNotPushed(ProcessProfileViewJob::class);
});
