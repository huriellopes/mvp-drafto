<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Posts;

use App\Enums\ModuleEnum;
use App\Enums\UserStatusEnum;
use App\Livewire\Public\Posts\PostComments;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    $this->author = User::factory()->writer()->withProfile()->create();
    $this->post = Post::factory()->published()->for($this->author)->create();
});

/**
 * Covers the guest redirect branch of save() (lines 57-58): when the create
 * gate denies a guest, they are sent to the login page.
 */
it('redirects a guest to login when comment creation is denied', function () {
    // O policy permite criação a convidados por padrão; mockamos a Gate para
    // forçar a negação e exercitar o branch de redirecionamento de convidado.
    Gate::shouldReceive('allows')
        ->with('create', Comment::class)
        ->andReturnFalse();

    Livewire::test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'Comentário de um visitante.')
        ->call('save')
        ->assertRedirect(route('login'));

    expect(Comment::where('post_id', $this->post->id)->exists())->toBeFalse();
});

/**
 * Covers the media-not-allowed branch of save() (lines 72-73): an authenticated
 * user whose COMMENTS module disallows images cannot post media.
 */
it('blocks an authenticated user without media permission from posting images', function () {
    $user = User::factory()->active()->create();

    $module = Module::where('slug', ModuleEnum::COMMENTS->value)->firstOrFail();
    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => true,
            'settings' => json_encode(['allow_images' => false]),
        ],
    ]);

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $this->post])
        ->set('form.content', 'Olha essa <img src="https://example.com/a.jpg"> imagem')
        ->call('save')
        ->assertHasErrors(['form.content']);

    expect(Comment::where('user_id', $user->id)->exists())->toBeFalse();
});

/**
 * Covers the reply-denied branch of saveReply() (lines 105-108): a suspended
 * user fails the reply gate even though comments are enabled.
 */
it('denies a reply when the reply gate rejects the user', function () {
    $parent = Comment::factory()->forPost($this->post)->create();
    $suspended = User::factory()->create(['status' => UserStatusEnum::SUSPENDED]);

    Livewire::actingAs($suspended)
        ->test(PostComments::class, ['post' => $this->post])
        ->set('replyingTo', $parent->id)
        ->set('replyContent', 'Tentativa de resposta de usuário suspenso.')
        ->call('saveReply')
        ->assertHasNoErrors();

    expect(Comment::where('parent_id', $parent->id)->where('user_id', $suspended->id)->exists())->toBeFalse();
});
