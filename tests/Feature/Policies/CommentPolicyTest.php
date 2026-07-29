<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Enums\ModuleEnum;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;

beforeEach(function (): void {
    $this->policy = new CommentPolicy;
});

function attachCommentsModule(User $user, array $settings): void
{
    $module = Module::where('slug', ModuleEnum::COMMENTS->value)->firstOrFail();
    $user->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => $settings],
    ]);
}

it('before grants everything to super admin', function (): void {
    $admin = User::factory()->superAdmin()->create();

    expect($this->policy->before($admin, 'delete'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'update'))->toBeNull();
});

it('viewAny always allows', function (): void {
    expect($this->policy->viewAny(null))->toBeTrue();
});

it('view allows visible comment and denies hidden', function (): void {
    expect($this->policy->view(null, Comment::factory()->visible()->create()))->toBeTrue();
    expect($this->policy->view(null, Comment::factory()->hidden()->create()))->toBeFalse();
});

it('create allows guest', function (): void {
    expect($this->policy->create(null))->toBeTrue();
});

it('create allows active user and denies suspended', function (): void {
    expect($this->policy->create(User::factory()->active()->create()))->toBeTrue();
    expect($this->policy->create(User::factory()->suspended()->create()))->toBeFalse();
});

it('reply denies inactive user', function (): void {
    $user = User::factory()->suspended()->create();
    $parent = Comment::factory()->create();

    expect($this->policy->reply($user, $parent))->toBeFalse();
});

it('reply allows when post comments enabled', function (): void {
    $post = Post::factory()->published()->create(['comments_enabled' => true]);
    $parent = Comment::factory()->forPost($post)->create();

    expect($this->policy->reply(User::factory()->active()->create(), $parent))->toBeTrue();
});

it('reply denies when post comments disabled', function (): void {
    $post = Post::factory()->published()->withoutComments()->create();
    $parent = Comment::factory()->forPost($post)->create();

    expect($this->policy->reply(User::factory()->active()->create(), $parent))->toBeFalse();
});

it('reply allows guest when comments enabled', function (): void {
    $post = Post::factory()->published()->create(['comments_enabled' => true]);
    $parent = Comment::factory()->forPost($post)->create();

    expect($this->policy->reply(null, $parent))->toBeTrue();
});

it('update allows owner of visible comment', function (): void {
    $user = User::factory()->active()->create();
    $comment = Comment::factory()->visible()->byUser($user)->create();

    expect($this->policy->update($user, $comment))->toBeTrue();
});

it('update denies non owner', function (): void {
    $comment = Comment::factory()->visible()->create();

    expect($this->policy->update(User::factory()->active()->create(), $comment))->toBeFalse();
});

it('update denies owner when comment hidden', function (): void {
    $user = User::factory()->active()->create();
    $comment = Comment::factory()->hidden()->byUser($user)->create();

    expect($this->policy->update($user, $comment))->toBeFalse();
});

it('update denies suspended owner', function (): void {
    $user = User::factory()->suspended()->create();
    $comment = Comment::factory()->visible()->byUser($user)->create();

    expect($this->policy->update($user, $comment))->toBeFalse();
});

it('delete allows comment owner', function (): void {
    $user = User::factory()->active()->create();
    $comment = Comment::factory()->byUser($user)->create();

    expect($this->policy->delete($user, $comment))->toBeTrue();
});

it('delete allows post author', function (): void {
    $author = User::factory()->writer()->active()->create();
    $post = Post::factory()->forAuthor($author)->create();
    $comment = Comment::factory()->forPost($post)->create();

    expect($this->policy->delete($author, $comment))->toBeTrue();
});

it('delete denies unrelated active user', function (): void {
    $comment = Comment::factory()->create();

    expect($this->policy->delete(User::factory()->active()->create(), $comment))->toBeFalse();
});

it('delete denies suspended owner', function (): void {
    $user = User::factory()->suspended()->create();
    $comment = Comment::factory()->byUser($user)->create();

    expect($this->policy->delete($user, $comment))->toBeFalse();
});

it('restore always denies', function (): void {
    expect($this->policy->restore(User::factory()->create(), Comment::factory()->create()))->toBeFalse();
});

it('forceDelete always denies', function (): void {
    expect($this->policy->forceDelete(User::factory()->create(), Comment::factory()->create()))->toBeFalse();
});

it('like allows active user on visible comment', function (): void {
    expect($this->policy->like(User::factory()->active()->create(), Comment::factory()->visible()->create()))->toBeTrue();
});

it('like denies on hidden comment', function (): void {
    expect($this->policy->like(User::factory()->active()->create(), Comment::factory()->hidden()->create()))->toBeFalse();
});

it('like denies suspended user', function (): void {
    expect($this->policy->like(User::factory()->suspended()->create(), Comment::factory()->visible()->create()))->toBeFalse();
});

it('moderate allows post author with moderation tools enabled', function (): void {
    $author = User::factory()->writer()->active()->create();
    attachCommentsModule($author, ['moderation_tools' => true]);

    $post = Post::factory()->forAuthor($author)->create();
    $comment = Comment::factory()->forPost($post)->create();

    expect($this->policy->moderate($author->fresh(), $comment))->toBeTrue();
});

it('moderate denies post author without moderation tools', function (): void {
    $author = User::factory()->writer()->active()->create();
    attachCommentsModule($author, ['moderation_tools' => false]);

    $post = Post::factory()->forAuthor($author)->create();
    $comment = Comment::factory()->forPost($post)->create();

    expect($this->policy->moderate($author->fresh(), $comment))->toBeFalse();
});

it('moderate denies user who is not post author', function (): void {
    $stranger = User::factory()->active()->create();
    attachCommentsModule($stranger, ['moderation_tools' => true]);

    $comment = Comment::factory()->create();

    expect($this->policy->moderate($stranger->fresh(), $comment))->toBeFalse();
});
