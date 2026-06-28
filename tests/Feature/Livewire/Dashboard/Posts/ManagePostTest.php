<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Dashboard\Posts\ManagePost;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCollection;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

beforeEach(function () {
    File::cleanDirectory(config('purifier.cachePath'));

    $this->user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $this->actingAs($this->user);
});

it('saves a draft containing border-radius styles without errors', function () {
    $content = '<p style="border-radius:8px;aspect-ratio:16/9;">Conteúdo do rascunho</p>';

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Rascunho com border-radius')
        ->set('form.content', $content)
        ->call('save')
        ->assertHasNoErrors();

    $post = Post::query()->where('title', 'Rascunho com border-radius')->first();

    expect($post)->not->toBeNull()
        ->and($post->status)->toBe(PostStatusEnum::DRAFT)
        ->and($post->content)->toContain('border-radius')
        ->and($post->content)->toContain('aspect-ratio');
});

it('publishes a post containing border-radius styles without errors', function () {
    $category = PostCategory::factory()->create(['user_id' => $this->user->id]);
    $content = '<p style="border-radius:12px;">Conteúdo publicado</p>';

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Publicação com border-radius')
        ->set('form.category_id', $category->id)
        ->set('form.content', $content)
        ->call('publish')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.posts.index'));

    $post = Post::query()->where('title', 'Publicação com border-radius')->first();

    expect($post)->not->toBeNull()
        ->and($post->status)->toBe(PostStatusEnum::PUBLISHED)
        ->and($post->published_at)->not->toBeNull()
        ->and($post->content)->toContain('border-radius');
});

it('autosaves an existing draft with border-radius styles without errors', function () {
    $post = Post::factory()->draft()->create([
        'user_id' => $this->user->id,
        'title' => 'Rascunho existente',
        'seo_enabled' => true,
    ]);

    $content = '<p style="border-radius:20px;">Atualização via autosave</p>';

    Livewire::test(ManagePost::class, ['post' => $post])
        ->set('form.content', $content)
        ->assertHasNoErrors();

    expect($post->refresh()->content)->toContain('border-radius');
});

it('renders the contextual help tips in the editor', function () {
    // Coleção disponível para que o campo (com seu help-tip) seja exibido.
    PostCollection::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(ManagePost::class)
        ->assertSee('Como salvar e publicar')      // help-tip dos botões de ação
        ->assertSee('Tipo de conteúdo')            // help-tip Post vs Artigo
        ->assertSee('conteúdo do dia a dia', false)
        ->assertSee('material aprofundado', false) // texto de Artigo
        ->assertSee('Coleções (séries)')           // help-tip de coleções
        ->assertSee('Slug da URL');                // help-tip de slug
});
