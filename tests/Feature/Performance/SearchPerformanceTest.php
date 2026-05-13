<?php

declare(strict_types=1);

use App\Actions\Public\GlobalSearchAction;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('a busca global deve ser eficiente com 5.000 registros', function () {
    // 1. Preparação Ultra-Rápida (5.000 registros)
    $startTime = microtime(true);

    echo "\n[Search Test] Preparando 5.000 registros via Bulk Insert...\n";

    $author = User::factory()->writer()->create();
    $category = PostCategory::factory()->create();
    $now = now()->toDateTimeString();

    // Inserimos 5.000 posts em blocos de 1k para velocidade máxima
    for ($i = 0; $i < 5; $i++) {
        $data = [];

        for ($j = 0; $j < 1000; $j++) {
            $title = 'Post de teste para busca ' . Str::random(10) . " termo-chave-{$i}";
            $data[] = [
                'user_id' => $author->id,
                'category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(5),
                'content' => 'Conteudo de teste',
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
                'type' => 'post',
            ];
        }
        DB::table('posts')->insert($data);
    }

    $preparationTime = microtime(true) - $startTime;
    echo '[Search Test] Tempo de preparação: ' . number_format($preparationTime, 2) . "s\n";

    // 2. Execução da Busca
    $action = app(GlobalSearchAction::class);

    $terms = ['termo-chave-0', 'busca', 'teste'];

    foreach ($terms as $term) {
        $startTime = microtime(true);
        $results = $action->exec($term);
        $executionTime = microtime(true) - $startTime;

        echo "[Search Test] Busca por '{$term}': " . number_format($executionTime * 1000, 2) . "ms\n";

        // Asserção: Busca deve ser < 45s em ambiente de teste simulado
        expect($executionTime)->toBeLessThan(45.0);
    }
});
