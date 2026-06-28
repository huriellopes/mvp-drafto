<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Admin\Updates\UpdateIndex;
use App\Models\PlatformUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

function makeDraftUpdate(): PlatformUpdate
{
    return PlatformUpdate::create([
        'title' => 'Comunicado Rascunho',
        'content' => 'Conteúdo do comunicado de novidades.',
        'audience' => 'all',
    ]);
}

it('creates a new draft update', function () {
    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->set('title', 'Nova Funcionalidade')
        ->set('content', 'Lançamos algo incrível para todos os usuários.')
        ->set('audience', 'writers')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('title', '');

    expect(PlatformUpdate::where('title', 'Nova Funcionalidade')->exists())->toBeTrue();
});

it('validates required fields when saving', function () {
    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->set('title', '')
        ->set('content', '')
        ->call('save')
        ->assertHasErrors(['title', 'content']);
});

it('loads a draft into the form for editing', function () {
    $update = makeDraftUpdate();

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('edit', $update->id)
        ->assertSet('editingId', $update->id)
        ->assertSet('title', 'Comunicado Rascunho')
        ->assertSet('audience', 'all');
});

it('does not load a sent update for editing', function () {
    $update = makeDraftUpdate();
    $update->update(['sent_at' => now(), 'recipients_count' => 3]);

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('edit', $update->id)
        ->assertSet('editingId', null);
});

it('returns silently when editing a missing update', function () {
    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('edit', 999999)
        ->assertSet('editingId', null);
});

it('updates an existing draft when saving in edit mode', function () {
    $update = makeDraftUpdate();

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('edit', $update->id)
        ->set('title', 'Título Editado')
        ->set('content', 'Conteúdo editado suficientemente longo.')
        ->call('save')
        ->assertHasNoErrors();

    expect($update->fresh()->title)->toBe('Título Editado');
});

it('cancels editing and resets the form', function () {
    $update = makeDraftUpdate();

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('edit', $update->id)
        ->call('cancelEdit')
        ->assertSet('editingId', null)
        ->assertSet('title', '');
});

it('exposes the update awaiting send confirmation', function () {
    $update = makeDraftUpdate();

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('confirmSend', $update->id)
        ->assertSet('sendingUpdate.id', $update->id);
});

it('returns silently when sending a missing update', function () {
    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->set('updateIdToSend', 999999)
        ->call('send')
        ->assertOk();
});

it('confirms and deletes an update', function () {
    $update = makeDraftUpdate();

    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('confirmDelete', $update->id)
        ->assertSet('updateIdToDelete', $update->id)
        ->assertDispatched('open-modal', name: 'confirm-delete-update')
        ->call('delete')
        ->assertSet('updateIdToDelete', null)
        ->assertDispatched('close-modal', name: 'confirm-delete-update');

    expect(PlatformUpdate::find($update->id))->toBeNull();
});

it('returns silently from delete when nothing is selected', function () {
    Livewire::actingAs($this->admin)
        ->test(UpdateIndex::class)
        ->call('delete')
        ->assertOk();
});
