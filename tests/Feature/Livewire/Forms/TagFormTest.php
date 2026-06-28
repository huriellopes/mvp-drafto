<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Forms\Dashboard\TagForm;
use App\Models\Tag;
use App\Models\User;
use Livewire\Component;
use Livewire\Livewire;

/**
 * TagForm has no host component in the codebase, so we drive it through a tiny
 * anonymous Livewire host.
 */
function tagFormHost(): Component
{
    return new class() extends Component
    {
        public TagForm $form;

        public ?int $createdId = null;

        public function save(): void
        {
            $this->createdId = $this->form->save()->id;
        }

        public function render()
        {
            return '<div></div>';
        }
    };
}

it('creates a tag with a generated slug', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(tagFormHost()::class)
        ->set('form.name', 'Inteligência Artificial')
        ->call('save')
        ->assertHasNoErrors();

    $tag = Tag::query()->where('user_id', $user->id)->first();

    expect($tag)->not->toBeNull()
        ->and($tag->name)->toBe('Inteligência Artificial')
        ->and($tag->slug)->toBe('inteligencia-artificial');
});

it('requires a name of at least two characters', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(tagFormHost()::class)
        ->set('form.name', 'a')
        ->call('save')
        ->assertHasErrors(['form.name']);
});

it('rejects a duplicate tag name', function () {
    $user = User::factory()->writer()->create();
    Tag::factory()->create(['user_id' => $user->id, 'name' => 'Laravel', 'slug' => 'laravel']);

    Livewire::actingAs($user)
        ->test(tagFormHost()::class)
        ->set('form.name', 'Laravel')
        ->call('save')
        ->assertHasErrors(['form.name']);
});

it('updates an existing tag owned by the user', function () {
    $user = User::factory()->writer()->create();
    $tag = Tag::factory()->create(['user_id' => $user->id, 'name' => 'Velho', 'slug' => 'velho']);

    $this->actingAs($user);

    $form = new TagForm(tagFormHost(), 'form');
    $form->setTag($tag);

    expect($form->editingTagId)->toBe($tag->id)
        ->and($form->name)->toBe('Velho');

    $form->name = 'Novo Nome';
    $updated = $form->save();

    expect($updated->id)->toBe($tag->id)
        ->and($updated->name)->toBe('Novo Nome')
        ->and($updated->slug)->toBe('novo-nome');
});
