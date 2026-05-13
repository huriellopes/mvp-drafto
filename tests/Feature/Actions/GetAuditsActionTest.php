<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\Admin\GetAuditsAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Models\Audit;

beforeEach(function () {
    Cache::flush();
});

test('getAvailableUsers returns a collection of user arrays', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    // Create an audit for this user
    Audit::create([
        'user_type' => User::class,
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => Post::class,
        'auditable_id' => 1,
        'old_values' => [],
        'new_values' => ['title' => 'Test'],
        'url' => 'http://localhost',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla',
    ]);

    $action = new GetAuditsAction();
    $users = $action->getAvailableUsers();

    expect($users)->toBeCollection()
        ->and($users->first())->toBeArray()
        ->and($users->first())->toHaveKeys(['id', 'name'])
        ->and($users->first()['name'])->toBe('John Doe');
});

test('getUniqueEvents returns a collection of event strings', function () {
    Audit::create([
        'event' => 'created',
        'auditable_type' => Post::class,
        'auditable_id' => 1,
        'old_values' => [],
        'new_values' => ['title' => 'Test'],
        'url' => 'http://localhost',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla',
    ]);

    $action = new GetAuditsAction();
    $events = $action->getUniqueEvents();

    expect($events)->toBeCollection()
        ->and($events->first())->toBe('created');
});

test('getUniqueTypes returns a collection of type arrays', function () {
    Audit::create([
        'event' => 'created',
        'auditable_type' => Post::class,
        'auditable_id' => 1,
        'old_values' => [],
        'new_values' => ['title' => 'Test'],
        'url' => 'http://localhost',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla',
    ]);

    $action = new GetAuditsAction();
    $types = $action->getUniqueTypes();

    expect($types)->toBeCollection()
        ->and($types->first())->toBeArray()
        ->and($types->first())->toHaveKeys(['value', 'label'])
        ->and($types->first()['label'])->toBe('Post');
});
