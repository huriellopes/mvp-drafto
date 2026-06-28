<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\SiteView;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('casts viewed_at and duration', function () {
    $view = SiteView::create([
        'url' => '/home',
        'duration' => '45',
        'viewed_at' => '2026-01-01 12:00:00',
    ]);

    expect($view->viewed_at)->toBeInstanceOf(Carbon::class)
        ->and($view->duration)->toBe(45)
        ->and($view->duration)->toBeInt();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $view = SiteView::create([
        'user_id' => $user->id,
        'url' => '/home',
        'viewed_at' => now(),
    ]);

    expect($view->user())->toBeInstanceOf(BelongsTo::class)
        ->and($view->user->id)->toBe($user->id);
});
