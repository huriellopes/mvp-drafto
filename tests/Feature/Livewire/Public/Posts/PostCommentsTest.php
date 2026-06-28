<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Posts;

use App\Enums\UserStatusEnum;
use App\Livewire\Public\Posts\PostComments;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->author = User::factory()->writer()->withProfile()->create();
    $this->post = Post::factory()->published()->for($this->author)->create();
});

it('renders the comments list', function () {
    $comment = Comment::factory()->forPost($this->post)->create(['content' => 'A visible comment here']);

    Livewire::test(PostComments::class, ['post' => $this->post])
        ->assertOk()
        ->assertSee($comment->content);
});

it('only exposes root visible comments in the computed property', function () {
    $root = Comment::factory()->forPost($this->post)->visible()->create();
    $hidden = Comment::factory()->forPost($this->post)->hidden()->create();
    $reply = Comment::factory()->replyTo($root)->create();

    Livewire::test(PostComments::class, ['post' => $this->post])
        ->assertSet('comments', fn ($comments) => $comments->contains('id', $root->id)
            && !$comments->contains('id', $hidden->id)
            && !$comments->contains('id', $reply->id));
});

it('lets an authenticated active user post a comment', function () {
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'This is a valid comment.')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::where('post_id', $this->post->id)->where('user_id', $user->id)->exists())->toBeTrue();
});

it('validates comment content length', function () {
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'ab')
        ->call('save')
        ->assertHasErrors(['form.content']);
});

it('rejects comments when comments are disabled', function () {
    $post = Post::factory()->published()->withoutComments()->for($this->author)->create();
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $post])
        ->set('form.content', 'Trying to comment.')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::where('post_id', $post->id)->exists())->toBeFalse();
});

it('allows guests to post a text comment on an open post', function () {
    Livewire::test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'A guest comment without media.')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::where('post_id', $this->post->id)->whereNull('user_id')->exists())->toBeTrue();
});

it('redirects guests to login from setReply when comments are disabled', function () {
    $post = Post::factory()->published()->withoutComments()->for($this->author)->create();
    $parent = Comment::factory()->forPost($post)->create();

    Livewire::test(PostComments::class, ['post' => $post])
        ->call('setReply', $parent->id)
        ->assertRedirect(route('login'));
});

it('denies commenting for a non-active user', function () {
    $suspended = User::factory()->create(['status' => UserStatusEnum::SUSPENDED]);

    Livewire::actingAs($suspended)
        ->test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'Suspended user comment')
        ->call('save');

    expect(Comment::where('user_id', $suspended->id)->exists())->toBeFalse();
});

it('blocks guests from sending media in comments', function () {
    Livewire::test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'Look <img src="x.jpg"> at this')
        ->call('save')
        ->assertHasErrors(['form.content']);
});

it('allows an active user with default settings to send media', function () {
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'An image <img src="https://example.com/a.jpg"> inside')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::where('user_id', $user->id)->exists())->toBeTrue();
});

it('sets the reply target', function () {
    $parent = Comment::factory()->forPost($this->post)->create();
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->call('setReply', $parent->id)
        ->assertSet('replyingTo', $parent->id)
        ->assertSet('form.parent_id', $parent->id);
});

it('cancels a reply', function () {
    $parent = Comment::factory()->forPost($this->post)->create();
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->call('setReply', $parent->id)
        ->call('cancelReply')
        ->assertSet('replyingTo', null)
        ->assertSet('replyContent', '');
});

it('lets an active user save a reply', function () {
    $parent = Comment::factory()->forPost($this->post)->create();
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->call('setReply', $parent->id)
        ->set('replyContent', 'This is my reply.')
        ->call('saveReply')
        ->assertHasNoErrors()
        ->assertSet('replyingTo', null);

    expect(Comment::where('parent_id', $parent->id)->where('user_id', $user->id)->exists())->toBeTrue();
});

it('validates reply content', function () {
    $parent = Comment::factory()->forPost($this->post)->create();
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->call('setReply', $parent->id)
        ->set('replyContent', 'no')
        ->call('saveReply')
        ->assertHasErrors(['replyContent']);
});

it('rejects replies when comments are disabled', function () {
    $post = Post::factory()->published()->withoutComments()->for($this->author)->create();
    $parent = Comment::factory()->forPost($post)->create();
    $user = User::factory()->active()->create();

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $post])
        ->set('replyingTo', $parent->id)
        ->set('replyContent', 'A reply attempt.')
        ->call('saveReply');

    expect(Comment::where('parent_id', $parent->id)->exists())->toBeFalse();
});
