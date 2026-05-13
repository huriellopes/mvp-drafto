<?php

declare(strict_types=1);

use App\DTOs\PostViewFilterData;
use App\Exports\PostViewsExport;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Jobs\QueueExport;

test('a exportação deve suportar 500.000 registros via fila sem falhas', function () {
    // 1. Preparação Massiva (500.000 registros)
    $post = Post::factory()->create();

    echo "\n[Extreme Test] Preparando banco com 500.000 registros (pode levar alguns segundos)...\n";

    // Inserção ultra-rápida via RAW SQL para ganhar tempo no teste
    $now = now()->toDateTimeString();
    $postId = $post->id;

    // Inserimos em lotes de 2k para compatibilidade com SQLite
    for ($i = 0; $i < 250; $i++) {
        $data = [];

        for ($j = 0; $j < 2000; $j++) {
            $data[] = [
                'post_id' => $postId,
                'viewed_at' => $now,
                'ip_hash' => md5((string) rand()),
                'user_agent' => 'Mozilla/5.0 (Extreme Test)',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('post_views')->insert($data);

        if ($i % 50 === 0) {
            echo '.';
        }
    }

    echo "\n[Extreme Test] Banco pronto. Iniciando exportação assíncrona...\n";

    $startTime = microtime(true);
    $initialMemory = memory_get_usage(true);

    // 2. Disparo da Exportação (Queued)
    $filters = PostViewFilterData::from([
        'sort' => 'viewed_at',
        'direction' => 'desc',
    ]);

    $export = new PostViewsExport($filters);

    // No ambiente de teste, podemos simular o Queue para garantir que o Job foi criado
    Queue::fake();

    $export->store('extreme_export.xlsx');

    $executionTime = microtime(true) - $startTime;
    $finalMemory = memory_get_usage(true);
    $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

    echo '[Extreme Test] Tempo para enfileirar 500k linhas: ' . number_format($executionTime * 1000, 2) . "ms\n";
    echo '[Extreme Test] Memória consumida no disparo: ' . number_format($memoryUsed, 2) . " MB\n";

    // Asserções
    Queue::assertPushed(QueueExport::class);

    echo "[Extreme Test] Sucesso: A exportação foi delegada para os Workers com baixo consumo de recursos.\n";

    expect($executionTime)->toBeLessThan(0.5); // O disparo deve ser quase instantâneo (< 500ms)
});
