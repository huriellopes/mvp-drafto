<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Actions\Posts\SavePostAction;
use App\Enums\RoleEnum;
use App\Livewire\Dashboard\Posts\ManagePost;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use RuntimeException;

/**
 * SavePostAction is final and cannot be mocked by Mockery, so we bind the
 * container abstract to a throwing stub. The component resolves it via
 * app(SavePostAction::class) and only calls ->exec(), so a duck-typed object
 * is enough to exercise the catch branches.
 */
function throwingSavePostAction(string $message): void
{
    app()->bind(SavePostAction::class, fn () => new class($message)
    {
        public function __construct(private string $message) {}

        public function exec(...$args): never
        {
            throw new RuntimeException($this->message);
        }
    });
}

beforeEach(function () {
    File::cleanDirectory(config('purifier.cachePath'));

    $this->user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $this->category = PostCategory::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);

    RateLimiter::clear('save-post:' . $this->user->id);
    RateLimiter::clear('publish-post:' . $this->user->id);
    RateLimiter::clear('schedule-post:' . $this->user->id);
});

/**
 * Covers the non-draft validation branch of save() (line 74): a published post
 * must pass the full validate() (category required), not just the draft rules.
 */
it('runs full validation when manually saving a published post', function () {
    $post = Post::factory()->published()->for($this->user)->create([
        'title' => 'Publicado Existente',
        'category_id' => $this->category->id,
        'seo_enabled' => true,
    ]);

    Livewire::test(ManagePost::class, ['post' => $post])
        ->set('form.category_id', '')
        ->call('save')
        ->assertHasErrors('form.category_id');
});

/**
 * Covers the manual-save rate limit branch (lines 100-101): a non-autosave save
 * beyond the 30-attempt window warns and does not throw.
 */
it('warns when too many manual saves happen', function () {
    for ($i = 0; $i < 30; $i++) {
        RateLimiter::hit('save-post:' . $this->user->id, 60);
    }

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Salvando Rápido Demais')
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('save')
        ->assertHasNoErrors();

    expect(Post::where('title', 'Salvando Rápido Demais')->exists())->toBeFalse();
});

/**
 * Covers the generic Exception catch in save() (lines 113-115).
 */
it('surfaces a generic error when the save action throws', function () {
    throwingSavePostAction('Falha no banco');

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Disparo de Erro Genérico')
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('save')
        ->assertHasNoErrors();

    expect(Post::where('title', 'Disparo de Erro Genérico')->exists())->toBeFalse();
});

/**
 * Covers the generic Exception catch in publish() (lines 157-158).
 */
it('surfaces a generic error when publishing and the action throws', function () {
    throwingSavePostAction('Falha ao publicar');

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Erro Genérico Publicar')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->call('publish')
        ->assertNoRedirect()
        ->assertHasNoErrors();
});

/**
 * Covers the generic Exception catch in schedule() (lines 213-214).
 */
it('surfaces a generic error when scheduling and the action throws', function () {
    throwingSavePostAction('Falha ao agendar');

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Erro Genérico Agendar')
        ->set('form.category_id', $this->category->id)
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.published_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->call('schedule')
        ->assertNoRedirect()
        ->assertHasNoErrors();

    expect(Post::where('title', 'Erro Genérico Agendar')->exists())->toBeFalse();
});
