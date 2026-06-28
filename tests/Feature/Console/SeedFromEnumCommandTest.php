<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\ModuleEnum;
use Illuminate\Support\Facades\DB;

it('seeds a table from a backed enum cases', function () {
    DB::table('modules')->delete();

    $this->artisan('app:seed-from-enum', [
        'table' => 'modules',
        'enum' => ModuleEnum::class,
        '--column' => 'slug',
        '--name-column' => 'name',
    ])->assertExitCode(0);

    expect(DB::table('modules')->count())->toBe(count(ModuleEnum::cases()))
        ->and(DB::table('modules')->where('slug', ModuleEnum::POST_SCHEDULER->value)->exists())->toBeTrue();
});

it('fails when the enum does not exist', function () {
    $this->artisan('app:seed-from-enum', [
        'table' => 'modules',
        'enum' => 'App\\Enums\\DoesNotExistEnum',
    ])->assertExitCode(1);
});
