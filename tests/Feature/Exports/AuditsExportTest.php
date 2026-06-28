<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\DTOs\AuditFilterData;
use App\Exports\AuditsExport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Models\Audit;

it('exposes the expected headings', function () {
    $export = new AuditsExport(new AuditFilterData());

    expect($export->headings())
        ->toBeArray()
        ->toBe([
            'ID',
            'Usuário',
            'Evento',
            'Modelo',
            'ID Modelo',
            'IP',
            'Data',
            'Valores Antigos',
            'Valores Novos',
        ]);
});

it('returns a builder query', function () {
    $export = new AuditsExport(new AuditFilterData());

    expect($export->query())->toBeInstanceOf(Builder::class);
});

it('uses a sane chunk size', function () {
    $export = new AuditsExport(new AuditFilterData());

    expect($export->chunkSize())->toBe(1000);
});

it('maps an audit row with a user', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    Audit::create([
        'user_type' => User::class,
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => Post::class,
        'auditable_id' => 7,
        'old_values' => ['title' => 'old'],
        'new_values' => ['title' => 'new'],
        'url' => 'http://localhost',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla',
    ]);

    $export = new AuditsExport(new AuditFilterData());
    $audit = $export->query()->first();

    $row = $export->map($audit);

    expect($row)->toBeArray()->toHaveCount(9)
        ->and($row[1])->toBe('John Doe')
        ->and($row[2])->toBe('created')
        ->and($row[3])->toBe('Post')
        ->and($row[4])->toBe(7)
        ->and($row[5])->toBe('127.0.0.1')
        ->and($row[7])->toBe(json_encode(['title' => 'old']))
        ->and($row[8])->toBe(json_encode(['title' => 'new']));
});

it('falls back to "Sistema" when the audit has no user', function () {
    Audit::create([
        'event' => 'updated',
        'auditable_type' => Post::class,
        'auditable_id' => 1,
        'old_values' => [],
        'new_values' => [],
        'url' => 'http://localhost',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla',
    ]);

    $export = new AuditsExport(new AuditFilterData());
    $audit = $export->query()->first();

    expect($export->map($audit)[1])->toBe('Sistema');
});
