<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Events\Posts\PostSaved;
use App\Listeners\BroadcastUnreadNotificationsChanged;
use App\Listeners\EnforceSingleSession;
use App\Listeners\Posts\HandlePostMediaAndSeo;
use Illuminate\Auth\Events\Login;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

// Regression: explicit Event::listen() on top of auto-discovery made each
// listener run twice (SEO/media job queued twice per post save).
it('registers each listener exactly once', function (string $event, string $listener) {
    $registered = collect(Event::getRawListeners()[$event] ?? [])
        ->map(fn ($l) => is_string($l) ? explode('@', $l)[0] : (is_array($l) ? $l[0] : null))
        ->filter(fn ($l) => $l === $listener);

    expect($registered)->toHaveCount(1);
})->with([
    'post saved' => [PostSaved::class, HandlePostMediaAndSeo::class],
    'login' => [Login::class, EnforceSingleSession::class],
    'notification sent' => [NotificationSent::class, BroadcastUnreadNotificationsChanged::class],
]);
