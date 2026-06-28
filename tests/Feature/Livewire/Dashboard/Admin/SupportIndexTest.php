<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Enums\SupportStatusEnum;
use App\Livewire\Dashboard\Admin\Support\SupportIndex;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

function makeTicket(User $user, array $attrs = []): Support
{
    return Support::query()->create(array_merge([
        'user_id' => $user->id,
        'subject' => 'Problema no login',
        'message' => 'Não consigo entrar na minha conta.',
        'status' => SupportStatusEnum::PENDING,
    ], $attrs));
}

it('blocks non-admins from the support management page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.support.index'))
        ->assertForbidden();
});

it('lets an admin open the support management page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.support.index'))
        ->assertOk()
        ->assertSeeLivewire(SupportIndex::class);
});

it('lists tickets and filters by search', function () {
    $admin = User::factory()->superAdmin()->create();
    $author = User::factory()->writer()->create();
    makeTicket($author, ['subject' => 'Erro de upload']);
    makeTicket($author, ['subject' => 'Outro assunto']);

    Livewire::actingAs($admin)
        ->test(SupportIndex::class)
        ->assertSee('Erro de upload')
        ->set('search', 'upload')
        ->assertSee('Erro de upload')
        ->assertDontSee('Outro assunto');
});

it('selects a ticket and pre-fills the response modal', function () {
    $admin = User::factory()->superAdmin()->create();
    $author = User::factory()->writer()->create();
    $ticket = makeTicket($author, ['admin_response' => 'resposta anterior']);

    Livewire::actingAs($admin)
        ->test(SupportIndex::class)
        ->call('selectSupport', $ticket->id)
        ->assertSet('selectedSupportId', $ticket->id)
        ->assertSet('adminResponse', 'resposta anterior')
        ->assertDispatched('open-modal', name: 'respond-support');
});

it('saves an admin response and updates the status', function () {
    $admin = User::factory()->superAdmin()->create();
    $author = User::factory()->writer()->create();
    $ticket = makeTicket($author);

    Livewire::actingAs($admin)
        ->test(SupportIndex::class)
        ->call('selectSupport', $ticket->id)
        ->set('adminResponse', 'Já resolvemos o seu problema.')
        ->set('newStatus', SupportStatusEnum::RESOLVED->value)
        ->call('saveResponse')
        ->assertSet('selectedSupportId', null)
        ->assertDispatched('close-modal', name: 'respond-support');

    $ticket->refresh();
    expect($ticket->admin_response)->toBe('Já resolvemos o seu problema.')
        ->and($ticket->status)->toBe(SupportStatusEnum::RESOLVED);
});
