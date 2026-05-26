<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\Posts\PublishScheduledPostsAction;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Posts\PostPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('publishes scheduled posts and notifies authors', function () {
    Notification::fake();
    
    $user = User::factory()->create();
    
    // Post agendado para o passado (deve ser publicado)
    $pastPost = Post::factory()->create([
        'user_id' => $user->id,
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => now()->subMinute(),
        'title' => 'Past Post',
    ]);
    
    // Post agendado para o futuro (não deve ser publicado)
    $futurePost = Post::factory()->create([
        'user_id' => $user->id,
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => now()->addHour(),
        'title' => 'Future Post',
    ]);
    
    $action = new PublishScheduledPostsAction();
    $count = $action->exec();
    
    expect($count)->toBe(1);
    
    $pastPost->refresh();
    $futurePost->refresh();
    
    expect($pastPost->status)->toBe(PostStatusEnum::PUBLISHED);
    expect($futurePost->status)->toBe(PostStatusEnum::SCHEDULED);
    
    Notification::assertSentTo($user, PostPublishedNotification::class, function ($notification) use ($pastPost) {
        return $notification->post->id === $pastPost->id;
    });
});
