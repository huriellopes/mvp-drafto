<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Billing;

use App\Livewire\Dashboard\Billing\PlansIndex;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Masmerise\Toaster\Toaster;

uses(RefreshDatabase::class);

beforeEach(function () {
    Toaster::fake();
});

it('redirects to stripe portal if user is already subscribed', function () {
    $user = User::factory()->create();

    // Simular assinatura ativa no Cashier
    $user->subscriptions()->create([
        'type' => 'plus',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_123',
        'quantity' => 1,
    ]);

    $plan = Plan::factory()->create([
        'slug' => 'pro',
        'stripe_id' => 'price_pro',
        'price' => 50,
    ]);

    Livewire::actingAs($user)
        ->test(PlansIndex::class)
        ->call('checkout', 'pro')
        ->assertRedirect(route('dashboard.billing.portal'));
});

it('shows error if plan has no stripe_id', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'slug' => 'broken-plan',
        'stripe_id' => null,
        'price' => 10,
    ]);

    Livewire::actingAs($user)
        ->test(PlansIndex::class)
        ->call('checkout', 'broken-plan');

    Toaster::assertDispatched('Este plano ainda não foi configurado no Stripe. Entre em contato com o suporte.');
});

it('does nothing if plan is free', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'slug' => 'free',
        'price' => 0,
    ]);

    Livewire::actingAs($user)
        ->test(PlansIndex::class)
        ->call('checkout', 'free')
        ->assertNoRedirect();
});
