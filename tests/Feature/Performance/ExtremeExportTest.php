<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\PostViews\PostViewIndex;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('a exportação de visualizações é delegada à fila sem processar inline', function () {
    // A exportação de grandes volumes não pode rodar na request: ela é
    // delegada a um worker via ExportDataJob. Logo, o disparo é O(1) —
    // independe da quantidade de registros — e deve ser barato em tempo/memória.
    Queue::fake();

    $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);

    $startTime = microtime(true);
    $initialMemory = memory_get_usage(true);

    Livewire::actingAs($admin)
        ->test(PostViewIndex::class)
        ->call('export')
        ->assertHasNoErrors();

    $executionTime = microtime(true) - $startTime;
    $memoryUsed = (memory_get_usage(true) - $initialMemory) / 1024 / 1024;

    echo "\n[Export Fila] Tempo de disparo: " . number_format($executionTime * 1000, 2) . "ms\n";
    echo '[Export Fila] Memória no disparo: ' . number_format($memoryUsed, 2) . " MB\n";

    // Garante que a geração foi delegada para os workers (não rodou inline).
    Queue::assertPushed(ExportDataJob::class);

    echo "[Export Fila] Sucesso: a exportação foi enfileirada com baixo consumo de recursos.\n";

    // O disparo deve ser quase instantâneo, independente do volume de dados.
    expect($executionTime)->toBeLessThan(1.0);
});
