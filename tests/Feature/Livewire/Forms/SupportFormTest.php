<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Dashboard\Support\SupportPage;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

it('stores a support ticket through the support page', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(SupportPage::class)
        ->set('form.subject', 'Não consigo publicar')
        ->set('form.message', 'Recebo um erro ao tentar publicar o meu post.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.subject', '')
        ->assertSet('form.message', '');

    expect(Support::query()->where('user_id', $user->id)->where('subject', 'Não consigo publicar')->exists())
        ->toBeTrue();
});

it('validates the support form subject and message length', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(SupportPage::class)
        ->set('form.subject', 'oi')
        ->set('form.message', 'curto')
        ->call('save')
        ->assertHasErrors(['form.subject', 'form.message']);

    expect(Support::query()->count())->toBe(0);
});

it('requires subject and message to be filled', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(SupportPage::class)
        ->set('form.subject', '')
        ->set('form.message', '')
        ->call('save')
        ->assertHasErrors([
            'form.subject' => 'required',
            'form.message' => 'required',
        ]);
});
