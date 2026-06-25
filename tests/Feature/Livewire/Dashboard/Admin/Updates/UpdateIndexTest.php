<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Admin\Updates\UpdateIndex;
use App\Mail\ProductUpdateMail;
use App\Models\PlatformUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

function draftUpdate(): PlatformUpdate
{
    return PlatformUpdate::create([
        'title' => 'Novo editor',
        'content' => 'Conteúdo do comunicado de novidades.',
        'audience' => 'all',
    ]);
}

it('confirms sending a draft suggesting a review first', function () {
    $update = draftUpdate();

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('confirmSend', $update->id)
        ->assertSet('updateIdToSend', $update->id)
        ->assertDispatched('open-modal', name: 'confirm-send-update')
        ->assertSee('revisar a novidade')
        ->assertSee('Enviar agora')
        ->assertSee('Revisar antes');
});

it('warns that a sent update was already delivered when resending', function () {
    $update = draftUpdate();
    $update->update(['sent_at' => now(), 'recipients_count' => 5]);

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('confirmSend', $update->id)
        ->assertSee('já foi enviado')
        ->assertSee('Enviar novamente')
        ->assertDontSee('Revisar antes');
});

it('sends the update and marks it as sent', function () {
    $update = draftUpdate();
    User::factory()->create(); // destinatário elegível (reader)

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('confirmSend', $update->id)
        ->call('send')
        ->assertDispatched('close-modal', name: 'confirm-send-update')
        ->assertSet('updateIdToSend', null);

    Mail::assertQueued(ProductUpdateMail::class);
    expect($update->fresh()->isSent())->toBeTrue();
});
