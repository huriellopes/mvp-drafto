<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\SendNewsletterJob;
use App\Livewire\Dashboard\Admin\Newsletter\NewsletterIndex;
use App\Models\NewsletterSubscriber;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

/**
 * Covers updatedFiltersSearch() (line 49-50): resetPage on search change.
 */
it('resets pagination when the subscriber search changes', function () {
    Livewire::actingAs($this->admin)
        ->test(NewsletterIndex::class)
        ->call('$refresh')
        ->set('filters.search', 'fan')
        ->assertOk();
});

/**
 * Covers the early return of delete() with nothing selected (line 61-62).
 */
it('returns silently from delete with no subscriber selected', function () {
    Livewire::actingAs($this->admin)
        ->test(NewsletterIndex::class)
        ->call('delete')
        ->assertOk();
});

/**
 * Covers the dispatch-lock-held branch of sendManualNewsletter() (lines 97-100).
 */
it('warns and aborts when a manual dispatch lock is already held', function () {
    // Pré-adquire o lock para forçar o branch de "já existe um disparo".
    Cache::lock('manual-newsletter-dispatch', 60)->get();

    Livewire::actingAs($this->admin)
        ->test(NewsletterIndex::class)
        ->set('customMessage', 'Mensagem informativa com mais de dez caracteres.')
        ->call('sendManualNewsletter')
        ->assertHasNoErrors();

    Queue::assertNotPushed(SendNewsletterJob::class);
});

/**
 * Covers the category-filtered branch of sendManualNewsletter() (lines 108-109).
 */
it('only dispatches to subscribers of the chosen category', function () {
    $category = PostCategory::factory()->create();

    $inCategory = NewsletterSubscriber::factory()->create();
    $inCategory->categories()->attach($category->id);

    NewsletterSubscriber::factory()->create(); // sem categoria

    Livewire::actingAs($this->admin)
        ->test(NewsletterIndex::class)
        ->set('customMessage', 'Mensagem segmentada com mais de dez caracteres.')
        ->set('manualCategoryId', $category->id)
        ->call('sendManualNewsletter')
        ->assertHasNoErrors();

    Queue::assertPushed(SendNewsletterJob::class, 1);
});
