<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Actions\Public\SubscribeNewsletterAction;
use App\DTOs\Public\NewsletterData;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('newsletter subscription is efficient under batch processing', function () {
    Mail::fake();
    $categories = PostCategory::factory()->count(10)->create();
    $action = app(SubscribeNewsletterAction::class);

    $startTime = microtime(true);
    $initialQueryCount = count(DB::getQueryLog());
    DB::enableQueryLog();

    $iterations = 100;
    echo "\n[Performance] Simulating $iterations newsletter subscriptions...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $action->exec(new NewsletterData(
            email: "user_{$i}@example.com",
            categoryId: $categories->random()->id,
        ));
    }

    $executionTime = microtime(true) - $startTime;
    $queriesPerRequest = count(DB::getQueryLog()) / $iterations;

    echo "[Performance] Total time for $iterations subscriptions: " . number_format($executionTime, 3) . "s\n";
    echo '[Performance] Average time per subscription: ' . number_format(($executionTime / $iterations) * 1000, 2) . "ms\n";
    echo '[Performance] Average DB queries per subscription: ' . number_format($queriesPerRequest, 1) . "\n";

    // Asserção: Cada inscrição deve levar menos de 200ms em média (considerando mocks de mail)
    expect($executionTime / $iterations)->toBeLessThan(0.2);

    // Asserção: Número de queries deve ser constante e baixo por requisição (aproximadamente 3-5)
    expect($queriesPerRequest)->toBeLessThan(6);
});
