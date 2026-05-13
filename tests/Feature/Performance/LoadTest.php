<?php

declare(strict_types=1);

use App\Actions\Public\ListPublicPostsAction;
use App\DTOs\Public\PostFilterData;
use App\Livewire\Public\Site\Home;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Testes de Performance & Carga
|--------------------------------------------------------------------------
| Este teste avalia o comportamento do sistema sob estresse simulado.
| Execute com: sail artisan test tests/Feature/Performance/LoadTest.php
*/

beforeEach(function () {
    // Limpa tabelas para garantir ambiente controlado
    DB::table('posts')->delete();
    DB::table('users')->delete();
});

test('a listagem de posts deve ser rápida com 1.000 registros', function () {
    // 1. Preparação (Massa de dados)
    $startTime = microtime(true);

    // Criamos um autor e 1000 posts publicados
    $author = User::factory()->writer()->create();
    Post::factory()->count(1000)->published()->public()->forAuthor($author)->create();

    $preparationTime = microtime(true) - $startTime;
    echo "\n[Performance] Tempo de preparação (1.000 posts): " . number_format($preparationTime, 2) . "s\n";

    // 2. Execução da Action
    $action = app(ListPublicPostsAction::class);

    $startTime = microtime(true);
    $results = $action->exec(PostFilterData::from(['per_page' => 10]));
    $executionTime = microtime(true) - $startTime;

    echo '[Performance] Listagem de Posts (Página 1 de 1.000): ' . number_format($executionTime * 1000, 2) . "ms\n";

    // Asserção: Listagem simples deve ser sub-100ms em ambiente de teste
    expect($executionTime)->toBeLessThan(0.1);
});

test('a renderização da home não deve degradar com muitos dados', function () {
    User::factory()->writer()->count(50)->create();
    Post::factory()->count(500)->published()->public()->create();

    $startTime = microtime(true);

    Livewire::test(Home::class)
        ->assertStatus(200);

    $executionTime = microtime(true) - $startTime;
    echo '[Performance] Renderização Home: ' . number_format($executionTime * 1000, 2) . "ms\n";

    expect($executionTime)->toBeLessThan(0.5);
});

test('o middleware de rastreio de visualização deve ser eficiente', function () {
    $post = Post::factory()->published()->public()->create();
    $slug = $post->slug;

    $startTime = microtime(true);

    // Simula 10 visitas consecutivas ao mesmo post
    for ($i = 0; $i < 10; $i++) {
        $this->get(route('posts.show', $slug));
    }

    $executionTime = microtime(true) - $startTime;
    $avgTime = $executionTime / 10;

    echo '[Performance] Média de visualização de post (com middleware): ' . number_format($avgTime * 1000, 2) . "ms\n";

    expect($avgTime)->toBeLessThan(0.2);
});
