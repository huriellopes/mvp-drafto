<?php

declare(strict_types=1);

namespace Tests\Feature\Accessibility;

use App\Livewire\Public\Site\ExplorePosts;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('exposes a skip link and a main landmark on public pages', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Pular para o conteúdo')
        ->assertSee('href="#main-content"', false)
        ->assertSee('id="main-content"', false);
});

it('exposes a skip link and a main landmark on dashboard pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertSee('href="#main-content"', false)
        ->assertSee('id="main-content"', false);
});

it('gives the global search an accessible name and dialog semantics', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('aria-label="Buscar por título, tag ou categoria"', false)
        ->assertSee('role="dialog"', false);
});

it('marks the report modal with dialog semantics and a labelled title', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('aria-labelledby="report-modal-title"', false)
        ->assertSee('id="report-modal-title"', false);
});

it('uses a single top-level h1 on the explore posts page', function () {
    // #[Lazy]: a primeira renderização é o placeholder; uma interação hidrata
    // e renderiza o conteúdo real (com o <h1>).
    $html = Livewire::test(ExplorePosts::class)
        ->call('$refresh')
        ->assertOk()
        ->assertSee('Biblioteca.</h1>', false)
        ->html();

    expect(mb_substr_count($html, '<h1'))->toBe(1);
});

it('exposes aria-pressed toggle state and an accessible comment field on a post page', function () {
    $author = User::factory()->writer()->withProfile()->create();
    $post = Post::factory()->published()->public()->for($author)->create();

    $this->get(route('posts.show', $post->slug))
        ->assertOk()
        ->assertSee('aria-pressed', false)
        ->assertSee('aria-label="Seu comentário"', false);
});
