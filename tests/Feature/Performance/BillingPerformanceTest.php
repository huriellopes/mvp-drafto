<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Livewire\Dashboard\Billing\PlansIndex;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('billing plans index rendering performance and query count', function () {
    $user = User::factory()->create();

    // Simular diversos planos ativos
    Plan::factory()->count(5)->create(['is_active' => true]);

    DB::enableQueryLog();
    $startTime = microtime(true);

    echo "\n[Performance] Testing Billing Plans Index rendering...\n";

    Livewire::actingAs($user)
        ->test(PlansIndex::class)
        ->assertStatus(200);

    $executionTime = microtime(true) - $startTime;
    $queryCount = count(DB::getQueryLog());

    echo '[Performance] Render time: ' . number_format($executionTime * 1000, 2) . "ms\n";
    echo '[Performance] DB queries for rendering: ' . $queryCount . "\n";

    // Asserção: Carregamento de página de planos não deve exceder 200ms em ambiente de teste
    expect($executionTime)->toBeLessThan(0.2);

    // Asserção: Número de queries deve ser otimizado (não deve disparar N+1 para planos)
    // Esperado: 1 query para o usuário, 1 para planos, talvez algumas para assinaturas do Cashier
    expect($queryCount)->toBeLessThan(15);
});
