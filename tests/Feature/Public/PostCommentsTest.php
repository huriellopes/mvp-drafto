<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Livewire\Public\Posts\PostComments;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    App::setLocale('pt_BR');
    $this->author = User::factory()->active()->withProfile()->create();
});

it('allows logged in users to comment', function () {
    $user = User::factory()->active()->withProfile()->create();
    $post = Post::factory()->published()->create([
        'user_id' => $this->author->id,
        'comments_enabled' => true
    ]);

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $post])
        ->set('form.content', 'This is a test comment')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::count())->toBe(1)
        ->and(Comment::first()->user_id)->toBe($user->id);
});

it('allows guests to comment if comments are enabled', function () {
    $post = Post::factory()->published()->create([
        'user_id' => $this->author->id,
        'comments_enabled' => true
    ]);

    Livewire::test(PostComments::class, ['post' => $post])
        ->set('form.content', 'Anonymous comment')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::count())->toBe(1)
        ->and(Comment::first()->user_id)->toBeNull();
});

it('does not allow guests to comment if comments are disabled', function () {
    $post = Post::factory()->published()->create([
        'user_id' => $this->author->id,
        'comments_enabled' => false
    ]);

    Livewire::test(PostComments::class, ['post' => $post])
        ->set('form.content', 'Anonymous comment')
        ->call('save')
        ->assertHasNoErrors();

    expect(Comment::count())->toBe(0);
});

it('allows replying as a guest', function () {
    $post = Post::factory()->published()->create([
        'user_id' => $this->author->id,
        'comments_enabled' => true
    ]);
    $comment = Comment::factory()->forPost($post)->create();

    Livewire::test(PostComments::class, ['post' => $post])
        ->set('replyingTo', $comment->id)
        ->set('replyContent', 'Anonymous reply')
        ->call('saveReply')
        ->assertHasNoErrors();

    expect(Comment::count())->toBe(2)
        ->and(Comment::latest('id')->first()->parent_id)->toBe($comment->id)
        ->and(Comment::latest('id')->first()->user_id)->toBeNull();
});

it('displays anonymous for guest comments', function () {
    $post = Post::factory()->published()->create([
        'user_id' => $this->author->id,
        'comments_enabled' => true
    ]);
    Comment::factory()->forPost($post)->create(['user_id' => null, 'content' => 'Hello world']);

    get(route('posts.show', $post->slug))
        ->assertStatus(200)
        ->assertSee('Anônimo')
        ->assertSee('Hello world');
});

it('allows guests to like a post', function () {
    $post = Post::factory()->published()->create(['likes_count' => 0]);

    Livewire::withQueryParams(['ip' => '1.2.3.4'])
        ->test(\App\Livewire\Actions\LikeButton::class, ['post' => $post])
        ->call('toggle');

    expect($post->fresh()->likes_count)->toBe(1);
    expect(DB::table('post_likes')->where('post_id', $post->id)->whereNull('user_id')->count())->toBe(1);

    // Unlike
    Livewire::withQueryParams(['ip' => '1.2.3.4'])
        ->test(\App\Livewire\Actions\LikeButton::class, ['post' => $post])
        ->call('toggle');

    expect($post->fresh()->likes_count)->toBe(0);
});

it('allows guests to like a comment', function () {
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->forPost($post)->create();

    Livewire::withQueryParams(['ip' => '1.2.3.4'])
        ->test(\App\Livewire\Actions\LikeComment::class, ['comment' => $comment])
        ->call('toggle');

    expect(DB::table('comment_likes')->where('comment_id', $comment->id)->whereNull('user_id')->count())->toBe(1);

    // Unlike
    Livewire::withQueryParams(['ip' => '1.2.3.4'])
        ->test(\App\Livewire\Actions\LikeComment::class, ['comment' => $comment])
        ->call('toggle');

    expect(DB::table('comment_likes')->where('comment_id', $comment->id)->whereNull('user_id')->count())->toBe(0);
});
