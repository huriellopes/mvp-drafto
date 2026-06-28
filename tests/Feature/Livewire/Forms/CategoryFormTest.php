<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Forms\Dashboard\CategoryForm;
use App\Models\PostCategory;
use App\Models\User;
use Livewire\Component;
use Livewire\Livewire;

/**
 * CategoryForm has no host component in the codebase, so we drive it through a
 * tiny anonymous Livewire host that mirrors how a real component would use it.
 */
function categoryFormHost(): Component
{
    return new class() extends Component
    {
        public CategoryForm $form;

        public function save(): void
        {
            $this->form->save();
        }

        public function render()
        {
            return '<div></div>';
        }
    };
}

it('creates a category for the authenticated user', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(categoryFormHost()::class)
        ->set('form.name', 'Tecnologia')
        ->set('form.slug', 'tecnologia')
        ->set('form.description', 'Posts sobre tecnologia')
        ->call('save')
        ->assertHasNoErrors();

    expect(PostCategory::query()
        ->where('user_id', $user->id)
        ->where('name', 'Tecnologia')
        ->exists())->toBeTrue();
});

it('requires a name of at least three characters', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(categoryFormHost()::class)
        ->set('form.name', 'ab')
        ->call('save')
        ->assertHasErrors(['form.name']);
});

it('rejects a duplicate category name for the same user', function () {
    $user = User::factory()->writer()->create();
    PostCategory::factory()->create([
        'user_id' => $user->id,
        'name' => 'Existente',
        'slug' => 'existente',
    ]);

    Livewire::actingAs($user)
        ->test(categoryFormHost()::class)
        ->set('form.name', 'Existente')
        ->call('save')
        ->assertHasErrors(['form.name']);
});

it('fills the form from an existing category', function () {
    $user = User::factory()->writer()->create();
    $category = PostCategory::factory()->create([
        'user_id' => $user->id,
        'name' => 'Viagem',
        'slug' => 'viagem',
        'description' => 'Sobre viagens',
    ]);

    $this->actingAs($user);

    $form = new CategoryForm(categoryFormHost(), 'form');
    $form->setCategory($category);

    expect($form->name)->toBe('Viagem')
        ->and($form->slug)->toBe('viagem')
        ->and($form->description)->toBe('Sobre viagens');
});
