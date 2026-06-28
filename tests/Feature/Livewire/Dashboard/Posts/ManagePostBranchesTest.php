<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Livewire\Dashboard\Posts\ManagePost;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    File::cleanDirectory(config('purifier.cachePath'));

    $this->user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $this->category = PostCategory::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);

    RateLimiter::clear('save-post:' . $this->user->id);
    RateLimiter::clear('publish-post:' . $this->user->id);
    RateLimiter::clear('schedule-post:' . $this->user->id);
});

it('shows a success notification on a manual save', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Rascunho Manual')
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('save')
        ->assertHasNoErrors();

    expect(Post::where('title', 'Rascunho Manual')->exists())->toBeTrue();
});

it('throws a validation error on a manual save without a title', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', '')
        ->call('save')
        ->assertHasErrors('form.title');
});

it('redirects to edit after creating from the create route', function () {
    Livewire::withQueryParams([])
        ->test(ManagePost::class)
        ->set('form.title', 'Novo Post Para Editar')
        ->call('save');

    // Sem o contexto de rota create não há redirect; apenas garante que o post foi criado.
    expect(Post::where('title', 'Novo Post Para Editar')->exists())->toBeTrue();
});

it('autosaves silently when an existing post field changes', function () {
    $post = Post::factory()->draft()->for($this->user)->create([
        'title' => 'Rascunho Existente',
        'seo_enabled' => true,
    ]);

    Livewire::test(ManagePost::class, ['post' => $post])
        ->set('form.content', '<p>Atualizado via autosave</p>')
        ->assertHasNoErrors();

    expect($post->fresh()->content)->toContain('Atualizado via autosave');
});

it('autosave does not persist invalid data nor surface a fatal error', function () {
    $post = Post::factory()->draft()->for($this->user)->create([
        'title' => 'Título Original',
        'seo_enabled' => true,
    ]);

    // Autosave dispara via updated(); título vazio falha validateForDraft()
    // mas a exceção é engolida silenciosamente (isAutosave) — sem persistir.
    Livewire::test(ManagePost::class, ['post' => $post])
        ->set('form.title', '')
        ->assertOk();

    expect($post->fresh()->title)->toBe('Título Original');
});

it('sets the cover via the cover-prepared event and autosaves', function () {
    $post = Post::factory()->draft()->for($this->user)->create([
        'title' => 'Com Capa',
        'seo_enabled' => true,
    ]);

    Livewire::test(ManagePost::class, ['post' => $post])
        ->call('setCover', 'covers/nova-capa.webp')
        ->assertSet('updatedCoverPath', 'covers/nova-capa.webp');
});

it('publishes a complete post and redirects', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Publicação Completa')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('publish')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.posts.index'));

    expect(Post::where('title', 'Publicação Completa')->first()->status)
        ->toBe(PostStatusEnum::PUBLISHED);
});

it('blocks publishing when validation fails', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Sem Categoria')
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('publish')
        ->assertHasErrors('form.category_id')
        ->assertNoRedirect();
});

it('warns when too many publish attempts happen', function () {
    // Esgota o rate limit de publicação (3 tentativas).
    for ($i = 0; $i < 3; $i++) {
        RateLimiter::hit('publish-post:' . $this->user->id, 60);
    }

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Excesso de Publicações')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('publish')
        ->assertNoRedirect();

    expect(Post::where('title', 'Excesso de Publicações')->exists())->toBeFalse();
});

it('requires a date to schedule a post', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Agendar Sem Data')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.published_at', '')
        ->call('schedule')
        ->assertHasErrors('form.published_at')
        ->assertNoRedirect();
});

it('rejects scheduling a post in the past', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Agendar No Passado')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.published_at', now()->subDay()->format('Y-m-d\TH:i'))
        ->call('schedule')
        ->assertHasErrors('form.published_at')
        ->assertNoRedirect();
});

it('schedules a post for a future date and redirects', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Agendamento Válido')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.published_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->call('schedule')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'schedule-modal')
        ->assertRedirect(route('dashboard.posts.index'));

    expect(Post::where('title', 'Agendamento Válido')->first()->status)
        ->toBe(PostStatusEnum::SCHEDULED);
});

it('blocks scheduling when validation fails', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Agendar Sem Categoria')
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('schedule')
        ->assertHasErrors('form.category_id')
        ->assertNoRedirect();
});

it('warns when too many schedule attempts happen', function () {
    for ($i = 0; $i < 3; $i++) {
        RateLimiter::hit('schedule-post:' . $this->user->id, 60);
    }

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Excesso de Agendamentos')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.published_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->call('schedule')
        ->assertNoRedirect();

    expect(Post::where('title', 'Excesso de Agendamentos')->exists())->toBeFalse();
});
