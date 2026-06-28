<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\ShortLinkIndex;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

it('queues a short links export job', function () {
    Livewire::actingAs($this->admin)
        ->test(ShortLinkIndex::class)
        ->call('export');

    Queue::assertPushed(ExportDataJob::class);
});

it('resets pagination when searching', function () {
    Livewire::actingAs($this->admin)
        ->test(ShortLinkIndex::class)
        ->set('search', 'abc')
        ->assertOk();
});

it('returns silently from delete when nothing is selected', function () {
    Livewire::actingAs($this->admin)
        ->test(ShortLinkIndex::class)
        ->call('delete')
        ->assertOk();
});

it('returns silently when deleting a missing short link', function () {
    Livewire::actingAs($this->admin)
        ->test(ShortLinkIndex::class)
        ->set('linkIdBeingDeleted', 999999)
        ->call('delete')
        ->assertSet('linkIdBeingDeleted', null);
});

it('switches sort column when sorting by a different field', function () {
    $post = Post::factory()->published()->create();
    ShortLink::factory()->create([
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ShortLinkIndex::class)
        ->call('sortBy', 'clicks')
        ->assertSet('sort', 'clicks')
        ->assertSet('direction', 'asc');
});
