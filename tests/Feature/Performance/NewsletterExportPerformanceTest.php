<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\DTOs\NewsletterFilterData;
use App\Exports\SubscribersExport;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

test('newsletter subscribers export is memory efficient with 5,000 records', function () {
    // 1. Massa de Dados (5.000 inscritos)
    echo "\n[Performance] Generating 5,000 subscribers for export test...\n";

    // Usamos insert para ser mais rápido que factory no loop de performance
    $subscribers = [];

    for ($i = 0; $i < 5000; $i++) {
        $subscribers[] = [
            'email' => "user_{$i}@example.com",
            'verified_at' => now(),
            'verification_token' => 'token_' . $i,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (count($subscribers) >= 1000) {
            NewsletterSubscriber::insert($subscribers);
            $subscribers = [];
        }
    }

    if (!empty($subscribers)) {
        NewsletterSubscriber::insert($subscribers);
    }

    $initialMemory = memory_get_peak_usage(true);
    $startTime = microtime(true);

    // 2. Execução da Exportação
    $filters = NewsletterFilterData::from([
        'sort' => 'created_at',
        'direction' => 'desc',
    ]);
    $export = new SubscribersExport($filters);

    // Simula o armazenamento em disco
    Excel::store($export, 'subscribers_test_export.xlsx');

    $executionTime = microtime(true) - $startTime;
    $finalMemory = memory_get_peak_usage(true);
    $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024;

    echo '[Performance] Time for 5,000 lines: ' . number_format($executionTime, 2) . "s\n";
    echo '[Performance] Peak memory addition: ' . number_format($memoryUsed, 2) . " MB\n";

    // Asserção: Exportação de 5k linhas não deve passar de 30 segundos
    expect($executionTime)->toBeLessThan(30);

    // Asserção: Memória adicional não deve explodir (limite generoso de 50MB para 5k registros com Excel)
    expect($memoryUsed)->toBeLessThan(50);

    // Limpeza
    if (file_exists(storage_path('app/subscribers_test_export.xlsx'))) {
        unlink(storage_path('app/subscribers_test_export.xlsx'));
    }
});
