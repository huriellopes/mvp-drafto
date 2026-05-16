<?php

declare(strict_types=1);

use App\Actions\Public\StoreSiteViewAction;
use App\DTOs\Public\StoreSiteViewData;
use App\Jobs\ProcessSiteViewJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('tracks a site view when visiting home page', function () {
    Queue::fake();

    $this->get(route('home'))
        ->assertStatus(200);

    Queue::assertPushed(ProcessSiteViewJob::class);
});

it('saves a site view in the database when job is processed', function () {
    $user = User::factory()->create();

    $data = new StoreSiteViewData(
        userId: $user->id,
        url: 'http://localhost/',
        ipAddress: '127.0.0.1',
        userAgent: 'Testing',
        sessionId: 'test-session',
        searchQuery: 'test search',
        duration: 0,
    );

    (new StoreSiteViewAction())->handle($data);

    $this->assertDatabaseHas('site_views', [
        'user_id' => $user->id,
        'url' => 'http://localhost/',
        'search_query' => 'test search',
    ]);
});
