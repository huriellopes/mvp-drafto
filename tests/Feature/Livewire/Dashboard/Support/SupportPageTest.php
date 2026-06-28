<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Support;

use App\Livewire\Dashboard\Support\SupportPage;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the support page', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard.support'))
        ->assertOk()
        ->assertSeeLivewire(SupportPage::class);
});

it('creates a support ticket', function () {
    Notification::fake();

    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SupportPage::class)
        ->set('form.subject', 'I need help')
        ->set('form.message', 'This is a detailed support message.')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('supports', [
        'user_id' => $user->id,
        'subject' => 'I need help',
    ]);
});

it('validates required ticket fields', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SupportPage::class)
        ->set('form.subject', 'no')
        ->set('form.message', 'short')
        ->call('save')
        ->assertHasErrors(['form.subject', 'form.message']);
});

it('selects a ticket and opens the response modal', function () {
    $user = User::factory()->withProfile()->create();
    $ticket = Support::create([
        'user_id' => $user->id,
        'subject' => 'Existing ticket',
        'message' => 'Some message body here.',
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    Livewire::test(SupportPage::class)
        ->call('selectTicket', $ticket->id)
        ->assertSet('selectedTicketId', $ticket->id)
        ->assertDispatched('open-modal', name: 'view-response');
});

it('only lists tickets belonging to the authenticated user', function () {
    $user = User::factory()->withProfile()->create();
    $other = User::factory()->withProfile()->create();

    Support::create([
        'user_id' => $user->id,
        'subject' => 'Mine ticket',
        'message' => 'My own message body.',
        'status' => 'pending',
    ]);
    Support::create([
        'user_id' => $other->id,
        'subject' => 'Other ticket',
        'message' => 'Someone else message.',
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    Livewire::test(SupportPage::class)
        ->assertSee('Mine ticket')
        ->assertDontSee('Other ticket');
});
