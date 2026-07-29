<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\DTOs\PostViewFilterData;
use App\Exports\PostViewsExport;
use App\Models\Post;
use App\Models\PostView;
use App\Models\User;

it('exposes the expected headings', function () {
    $export = new PostViewsExport(new PostViewFilterData);

    expect($export->headings())->toBe([
        'ID Registro',
        'Título do Post',
        'Nome do Leitor',
        'Data e Hora da Visualização',
        'Endereço IP (Descriptografado)',
        'Navegador / Dispositivo',
    ]);
});

it('uses a sane chunk size', function () {
    expect((new PostViewsExport(new PostViewFilterData))->chunkSize())->toBe(1000);
});

it('builds a query returning seeded rows', function () {
    PostView::factory()->count(2)->create();

    $export = new PostViewsExport(new PostViewFilterData);

    expect($export->query()->count())->toBe(2);
});

it('applies the search filter against the post title', function () {
    $post = Post::factory()->published()->create(['title' => 'Findable Title']);
    PostView::factory()->forPost($post)->create();
    PostView::factory()->create();

    $export = new PostViewsExport(new PostViewFilterData(search: 'Findable'));

    expect($export->query()->count())->toBe(1);
});

it('maps a row with an authenticated reader', function () {
    $user = User::factory()->create(['name' => 'Jane Reader']);
    $post = Post::factory()->published()->create(['title' => 'Hello World']);
    PostView::factory()->forPost($post)->byUser($user)->create([
        'ip_hash' => '10.0.0.1',
        'user_agent' => 'TestAgent',
    ]);

    $export = new PostViewsExport(new PostViewFilterData);
    $row = $export->map($export->query()->first());

    expect($row)->toHaveCount(6)
        ->and($row[1])->toBe('Hello World')
        ->and($row[2])->toBe('Jane Reader')
        ->and($row[4])->toBe('10.0.0.1')
        ->and($row[5])->toBe('TestAgent');
});

it('maps a row for an anonymous visitor', function () {
    PostView::factory()->anonymous()->create();

    $export = new PostViewsExport(new PostViewFilterData);

    expect($export->map($export->query()->first())[2])->toBe('Visitante Anônimo');
});
