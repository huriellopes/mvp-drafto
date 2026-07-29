<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\DTOs\UserFilterData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Exports\UsersExport;
use App\Models\User;
use Mockery;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

it('exposes the expected headings', function () {
    $export = new UsersExport(new UserFilterData);

    expect($export->headings())->toBe([
        'ID',
        'Nome Completo',
        'E-mail',
        'Papel (Role)',
        'Status',
        'Membro Desde',
        'Último Login',
    ]);
});

it('bolds the header row in styles', function () {
    $export = new UsersExport(new UserFilterData);
    $styles = $export->styles(Mockery::mock(Worksheet::class));

    expect($styles)->toBe([1 => ['font' => ['bold' => true]]]);
});

it('builds a query returning seeded rows', function () {
    User::factory()->count(3)->create();

    expect((new UsersExport(new UserFilterData))->query()->count())->toBe(3);
});

it('filters by search, role and status', function () {
    User::factory()->create([
        'name' => 'Searchable Person',
        'role' => RoleEnum::WRITER,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    User::factory()->create(['name' => 'Someone Else', 'role' => RoleEnum::READER]);

    expect((new UsersExport(new UserFilterData(search: 'Searchable')))->query()->count())->toBe(1)
        ->and((new UsersExport(new UserFilterData(role: RoleEnum::WRITER->value)))->query()->count())->toBe(1)
        ->and((new UsersExport(new UserFilterData(status: UserStatusEnum::ACTIVE->value)))->query()->count())
        ->toBeGreaterThanOrEqual(1);
});

it('maps a user row that has logged in', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => RoleEnum::WRITER,
        'status' => UserStatusEnum::ACTIVE,
        'last_login_at' => now(),
    ]);

    $export = new UsersExport(new UserFilterData);
    $row = $export->map($user->fresh());

    expect($row)->toHaveCount(7)
        ->and($row[0])->toBe($user->id)
        ->and($row[1])->toBe('John Doe')
        ->and($row[2])->toBe('john@example.com')
        ->and($row[3])->toBe(RoleEnum::WRITER->label())
        ->and($row[4])->toBe(UserStatusEnum::ACTIVE->label())
        ->and($row[6])->not->toBe('Nunca');
});

it('shows "Nunca" when the user never logged in', function () {
    $user = User::factory()->create(['last_login_at' => null]);

    $export = new UsersExport(new UserFilterData);

    expect($export->map($user->fresh())[6])->toBe('Nunca');
});
