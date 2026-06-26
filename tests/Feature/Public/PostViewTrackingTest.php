<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\PostVisibilityEnum;
use App\Jobs\ProcessPostViewJob;
use App\Livewire\Public\Posts\ShowPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->author = User::factory()->writer()->withProfile()->create();
    $this->post = Post::factory()->published()->for($this->author)->create([
        'visibility' => PostVisibilityEnum::PUBLIC,
        'views_count' => 0,
    ]);
});

/**
 * Regressão: o ShowPost NÃO deve incrementar views_count diretamente.
 * A contagem é responsabilidade única do pipeline
 * (TrackPostView -> ProcessPostViewJob -> drafto:sync-views), que faz dedup e respeita LGPD.
 */
it('does not increment views_count directly on render', function () {
    Livewire::test(ShowPost::class, ['slug' => $this->post->slug])->assertOk();

    expect($this->post->fresh()->views_count)->toBe(0);
});

it('dispatches the view job for a visitor with analytics consent', function () {
    Queue::fake();

    $this->withUnencryptedCookie('drafto_consent', json_encode(['analytics' => true, 'marketing' => false]))
        ->get(route('posts.show', $this->post->slug))
        ->assertStatus(200);

    Queue::assertPushed(ProcessPostViewJob::class);
});

it('does not dispatch the view job without analytics consent', function () {
    Queue::fake();

    $this->get(route('posts.show', $this->post->slug))
        ->assertStatus(200);

    Queue::assertNotPushed(ProcessPostViewJob::class);
});

it('does not dispatch the view job when the author views their own post', function () {
    Queue::fake();

    actingAs($this->author)
        ->withUnencryptedCookie('drafto_consent', json_encode(['analytics' => true, 'marketing' => false]))
        ->get(route('posts.show', $this->post->slug))
        ->assertStatus(200);

    Queue::assertNotPushed(ProcessPostViewJob::class);
});
