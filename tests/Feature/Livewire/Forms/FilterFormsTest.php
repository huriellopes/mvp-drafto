<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Dashboard\Admin\Newsletter\NewsletterIndex;
use App\Livewire\Dashboard\Admin\Reports\ReportIndex;
use App\Livewire\Forms\Admin\NewsletterFilterForm;
use App\Livewire\Forms\Admin\ReportFilterForm;
use App\Models\User;
use Livewire\Livewire;

it('sorts newsletter subscribers and toggles the direction', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(NewsletterIndex::class)
        ->assertSet('filters.sort', 'created_at')
        ->assertSet('filters.direction', 'desc')
        ->call('sortBy', 'email')
        ->assertSet('filters.sort', 'email')
        ->assertSet('filters.direction', 'asc')
        ->call('sortBy', 'email')
        ->assertSet('filters.direction', 'desc');
});

it('resets the newsletter filter form to its defaults', function () {
    $form = new NewsletterFilterForm(new NewsletterIndex(), 'filters');
    $form->search = 'algo';
    $form->category_id = 5;
    $form->sort = 'email';
    $form->direction = 'asc';

    $form->resetFilters();

    expect($form->search)->toBe('')
        ->and($form->category_id)->toBeNull()
        ->and($form->sort)->toBe('created_at')
        ->and($form->direction)->toBe('desc');
});

it('resets report filters when switching tabs', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->set('filters.search', 'spam')
        ->set('filters.status', 'pending')
        ->call('setTab', 'moderation')
        ->assertSet('tab', 'moderation')
        ->assertSet('filters.search', '')
        ->assertSet('filters.status', '');
});

it('toggles the report sort direction', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->call('sortBy', 'status')
        ->assertSet('filters.sort', 'status')
        ->assertSet('filters.direction', 'asc')
        ->call('sortBy', 'status')
        ->assertSet('filters.direction', 'desc');
});

it('resets the report filter form to its defaults', function () {
    $form = new ReportFilterForm(new ReportIndex(), 'filters');
    $form->search = 'x';
    $form->status = 'pending';
    $form->reason = 'spam';
    $form->sort = 'status';
    $form->direction = 'asc';

    $form->resetFilters();

    expect($form->search)->toBe('')
        ->and($form->status)->toBe('')
        ->and($form->reason)->toBe('')
        ->and($form->sort)->toBe('created_at')
        ->and($form->direction)->toBe('desc');
});
