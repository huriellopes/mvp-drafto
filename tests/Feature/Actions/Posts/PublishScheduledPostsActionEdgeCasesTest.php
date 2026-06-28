<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Posts;

use App\Actions\Posts\PublishScheduledPostsAction;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->action = app(PublishScheduledPostsAction::class);
});

it('logs an error and keeps the post unpublished when notification dispatch throws', function () {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->create([
        'user_id' => $author->id,
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => now()->subMinute(),
    ]);

    Log::spy();

    // Force the notification dispatch (inside the DB transaction) to throw an
    // Exception so the catch block (lines 37-38) is exercised and the
    // transaction rolls back the status update.
    $this->mock(Dispatcher::class, function ($mock) {
        $mock->shouldReceive('send')->andThrow(new Exception('notify failed'));
        $mock->shouldReceive('sendNow')->andThrow(new Exception('notify failed'));
    });

    $count = $this->action->exec();

    expect($count)->toBe(0)
        ->and($post->fresh()->status)->toBe(PostStatusEnum::SCHEDULED);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => str_contains($message, 'Erro ao publicar post agendado'));
});
