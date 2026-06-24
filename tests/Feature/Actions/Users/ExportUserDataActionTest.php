<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\ExportUserDataAction;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;

it('exports the user personal data as a structured array', function () {
    $user = User::factory()->create(['email' => 'me@example.com']);
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'me-export']);
    Post::factory()->create(['user_id' => $user->id, 'title' => 'Meu artigo']);

    $data = app(ExportUserDataAction::class)->exec($user);

    expect($data['account']['email'])->toBe('me@example.com')
        ->and($data['profile']['username'])->toBe('me-export')
        ->and($data['posts'])->toHaveCount(1)
        ->and($data['posts'][0]['title'])->toBe('Meu artigo')
        ->and($data)->toHaveKeys(['exported_at', 'account', 'profile', 'posts', 'comments', 'collections']);
});

it('does not leak sensitive fields in the export', function () {
    $user = User::factory()->create();

    $data = app(ExportUserDataAction::class)->exec($user);

    expect($data['account'])->not->toHaveKeys(['password', 'remember_token', 'two_factor_secret']);
});
