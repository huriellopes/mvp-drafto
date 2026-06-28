<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\ExportDataJob;
use App\Jobs\SendNewsletterJob;
use App\Livewire\Dashboard\Admin\Newsletter\NewsletterIndex;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
});

it('blocks non-admins from the newsletter page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.newsletter.index'))
        ->assertForbidden();
});

it('lets an admin open the newsletter page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.newsletter.index'))
        ->assertOk()
        ->assertSeeLivewire(NewsletterIndex::class);
});

it('lists subscribers', function () {
    $admin = User::factory()->superAdmin()->create();
    NewsletterSubscriber::factory()->create(['email' => 'fan@example.com']);

    Livewire::actingAs($admin)
        ->test(NewsletterIndex::class)
        ->call('$refresh')
        ->assertSee('fan@example.com');
});

it('confirms and deletes a subscriber', function () {
    $admin = User::factory()->superAdmin()->create();
    $subscriber = NewsletterSubscriber::factory()->create();

    Livewire::actingAs($admin)
        ->test(NewsletterIndex::class)
        ->call('confirmDeletion', $subscriber->id)
        ->assertSet('subscriberIdBeingDeleted', $subscriber->id)
        ->assertDispatched('open-modal', name: 'confirm-subscriber-deletion')
        ->call('delete')
        ->assertSet('subscriberIdBeingDeleted', null);
});

it('queues an export job', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(NewsletterIndex::class)
        ->call('export');

    Queue::assertPushed(ExportDataJob::class);
});

it('validates the manual newsletter message before dispatching', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(NewsletterIndex::class)
        ->set('customMessage', 'curto')
        ->call('sendManualNewsletter')
        ->assertHasErrors(['customMessage']);

    Queue::assertNotPushed(SendNewsletterJob::class);
});

it('dispatches manual newsletter jobs for each subscriber', function () {
    $admin = User::factory()->superAdmin()->create();
    NewsletterSubscriber::factory()->count(2)->create();

    Livewire::actingAs($admin)
        ->test(NewsletterIndex::class)
        ->set('customMessage', 'Mensagem informativa com mais de dez caracteres.')
        ->call('sendManualNewsletter')
        ->assertHasNoErrors();

    Queue::assertPushed(SendNewsletterJob::class, 2);
});
