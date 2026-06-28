<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\DTOs\ShortLinkFilterData;
use App\Exports\ShortLinksExport;
use App\Models\Post;
use App\Models\Profile;
use App\Models\ShortLink;
use App\Models\User;
use Mockery;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

it('exposes the expected headings', function () {
    $export = new ShortLinksExport(new ShortLinkFilterData());

    expect($export->headings())->toBe([
        'ID',
        'Código',
        'URL Encurtada',
        'Usuário',
        'E-mail Usuário',
        'Tipo',
        'Destino Original',
        'Cliques',
        'Criado em',
    ]);
});

it('bolds the header row in styles', function () {
    $export = new ShortLinksExport(new ShortLinkFilterData());
    $styles = $export->styles(Mockery::mock(Worksheet::class));

    expect($styles)->toBe([1 => ['font' => ['bold' => true]]]);
});

it('builds a query returning seeded rows', function () {
    $post = Post::factory()->published()->create();
    ShortLink::factory()->create([
        'shortable_type' => Post::class,
        'shortable_id' => $post->id,
    ]);

    expect((new ShortLinksExport(new ShortLinkFilterData()))->query()->count())->toBe(1);
});

it('filters by code', function () {
    $post = Post::factory()->published()->create();
    ShortLink::factory()->create([
        'code' => 'ABC123',
        'shortable_type' => Post::class,
        'shortable_id' => $post->id,
    ]);
    ShortLink::factory()->create([
        'code' => 'ZZZ999',
        'shortable_type' => Post::class,
        'shortable_id' => $post->id,
    ]);

    expect((new ShortLinksExport(new ShortLinkFilterData(search: 'ABC')))->query()->count())->toBe(1);
});

it('maps a row pointing to a post', function () {
    $owner = User::factory()->create(['name' => 'Owner Name', 'email' => 'owner@example.com']);
    $post = Post::factory()->published()->create(['title' => 'My Post Title']);
    ShortLink::factory()->create([
        'user_id' => $owner->id,
        'code' => 'POST01',
        'clicks' => 5,
        'shortable_type' => Post::class,
        'shortable_id' => $post->id,
    ]);

    $export = new ShortLinksExport(new ShortLinkFilterData());
    $row = $export->map($export->query()->first());

    expect($row)->toHaveCount(9)
        ->and($row[1])->toBe('POST01')
        ->and($row[2])->toContain('POST01')
        ->and($row[3])->toBe('Owner Name')
        ->and($row[4])->toBe('owner@example.com')
        ->and($row[5])->toBe('Post')
        ->and($row[6])->toBe('My Post Title')
        ->and($row[7])->toBe(5);
});

it('maps a row pointing to a user profile', function () {
    $owner = User::factory()->create();
    $target = User::factory()->has(Profile::factory()->state(['username' => 'targetuser']))->create([
        'name' => 'Target Name',
    ]);
    ShortLink::factory()->create([
        'user_id' => $owner->id,
        'shortable_type' => User::class,
        'shortable_id' => $target->id,
    ]);

    $export = new ShortLinksExport(new ShortLinkFilterData());
    $row = $export->map($export->query()->first());

    expect($row[5])->toBe('Perfil')
        ->and($row[6])->toBe('targetuser');
});
