<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\DeleteMyAccountAction;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;

it('deletes the user and archives it in deleted_models', function () {
    $user = User::factory()->create();

    $result = app(DeleteMyAccountAction::class)->exec($user);

    expect($result)->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeFalse();

    $this->assertDatabaseHas('deleted_models', [
        'key' => $user->id,
        'model' => $user->getMorphClass(),
    ]);
});

it('cascade deletes data related to the user', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    app(DeleteMyAccountAction::class)->exec($user);

    $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});
