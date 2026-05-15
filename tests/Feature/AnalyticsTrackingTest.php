<?php

declare(strict_types=1);

use App\Models\SiteView;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessSiteViewJob;

it('tracks a site view when visiting home page', function () {
    Queue::fake();

    $this->get(route('home'))
        ->assertStatus(200);

    Queue::assertPushed(ProcessSiteViewJob::class);
});

it('saves a site view in the database when job is processed', function () {
    $user = User::factory()->create();
    
    $data = new \App\DTOs\Public\StoreSiteViewData(
        userId: $user->id,
        url: 'http://localhost/',
        ipAddress: '127.0.0.1',
        userAgent: 'Testing',
        sessionId: 'test-session',
        searchQuery: 'test search',
        duration: 0
    );

    (new \App\Actions\Public\StoreSiteViewAction())->handle($data);

    $this->assertDatabaseHas('site_views', [
        'user_id' => $user->id,
        'url' => 'http://localhost/',
        'search_query' => 'test search',
    ]);
});
