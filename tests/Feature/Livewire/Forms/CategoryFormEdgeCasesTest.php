<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Forms\Dashboard\CategoryForm;
use App\Models\PostCategory;
use App\Models\User;
use Livewire\Component;
use Livewire\Livewire;

/**
 * Tiny host to drive CategoryForm, mirroring the existing CategoryFormTest setup.
 */
function categoryEditHost(): Component
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

/**
 * Covers the edit branch of save() (lines 58-61): when editingCategoryId is set,
 * the owned category is resolved and updated through the action.
 */
it('updates an existing owned category when editingCategoryId is set', function () {
    $user = User::factory()->writer()->create();
    $category = PostCategory::factory()->create([
        'user_id' => $user->id,
        'name' => 'Antiga',
        'slug' => 'antiga',
    ]);

    Livewire::actingAs($user)
        ->test(categoryEditHost()::class)
        ->set('form.editingCategoryId', $category->id)
        ->set('form.name', 'Atualizada')
        ->set('form.slug', 'atualizada')
        ->call('save')
        ->assertHasNoErrors();

    expect($category->fresh()->name)->toBe('Atualizada');
});
