<?php

declare(strict_types=1);

use App\DTOs\PostViewFilterData;
use App\Exports\PostViewsExport;
use App\Models\Post;
use App\Models\PostView;
use Maatwebsite\Excel\Facades\Excel;

test('a exportação de visualizações deve ser eficiente com 10.000 registros', function () {
    // 1. Massa de Dados (10.000 visualizações)
    $post = Post::factory()->create();

    echo "\n[Export] Gerando 10.000 registros para teste...\n";

    // Geramos em blocos para não estourar a memória do teste
    for ($i = 0; $i < 10; $i++) {
        PostView::factory()->count(1000)->create([
            'post_id' => $post->id,
            'viewed_at' => now(),
        ]);
    }

    $initialMemory = memory_get_peak_usage(true);
    $startTime = microtime(true);

    // 2. Execução da Exportação
    $filters = PostViewFilterData::from([
        'sort' => 'viewed_at',
        'direction' => 'desc',
    ]);

    $export = new PostViewsExport($filters);

    // Simula o download (armazenando em disco temporariamente)
    Excel::store($export, 'test_export.xlsx');

    $executionTime = microtime(true) - $startTime;
    $finalMemory = memory_get_peak_usage(true);
    $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

    echo '[Export] Tempo para 10.000 linhas: ' . number_format($executionTime, 2) . "s\n";
    echo '[Export] Pico de memória adicional: ' . number_format($memoryUsed, 2) . " MB\n";

    // Asserção: Exportação local de 10k linhas não deve passar de 120 segundos
    expect($executionTime)->toBeLessThan(120);

    // Limpeza
    if (file_exists(storage_path('app/test_export.xlsx'))) {
        unlink(storage_path('app/test_export.xlsx'));
    }
});
