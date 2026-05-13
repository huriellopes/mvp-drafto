<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Livewire\Public\Posts\PostComments;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('security: comments have rate limiting protection', function () {
    $user = User::factory()->hasProfile()->create();
    $post = Post::factory()->create(['comments_enabled' => true]);

    $component = Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $post]);

    // Simular 5 comentários (limite)
    for ($i = 0; $i < 5; $i++) {
        $component->set('form.content', "Valid comment $i")
            ->call('save')
            ->assertHasNoErrors();
    }

    // O 6º comentário deve ser bloqueado pelo RateLimiter
    $component->set('form.content', 'Spam comment 6')
        ->call('save')
        ->assertHasErrors(['form.content' => 'Muitas tentativas. Aguarde um minuto para comentar novamente.']);

    // Verifica se apenas 5 foram salvos
    expect($post->comments()->count())->toBe(5);
});

test('security: comment image blocking cannot be bypassed with case variation', function () {
    $user = User::factory()->hasProfile()->create();
    $post = Post::factory()->create(['comments_enabled' => true]);

    // O PenTest tenta burlar com <IMG (maiúsculas)
    $maliciousContent = '<IMG SRC="x" ONERROR="alert(1)">';

    Livewire::actingAs($user)
        ->test(PostComments::class, ['post' => $post])
        ->set('form.content', $maliciousContent)
        ->call('save')
        ->assertHasErrors(['form.content' => 'Seu plano atual não permite o envio de mídia nos comentários.']);

    // Verifica se o comentário NÃO foi salvo
    expect($post->comments()->count())->toBe(0);
});
