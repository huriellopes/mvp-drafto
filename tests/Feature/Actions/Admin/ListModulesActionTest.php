<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\ListModulesAction;
use App\DTOs\ModuleFilterData;
use App\Enums\ModuleEnum;
use App\Models\Module;

beforeEach(function () {
    $this->action = app(ListModulesAction::class);
});

it('paginates all seeded modules', function () {
    $result = $this->action->exec(new ModuleFilterData(perPage: 50));

    expect($result->total())->toBe(Module::count())
        ->and($result->perPage())->toBe(50);
});

it('filters modules by search term against name', function () {
    $module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();
    $module->update(['name' => 'Super Unique Widget']);

    $result = $this->action->exec(new ModuleFilterData(search: 'Super Unique'));

    expect($result->pluck('id')->all())->toContain($module->id)
        ->and($result->total())->toBe(1);
});

it('honors the requested per page value', function () {
    $result = $this->action->exec(new ModuleFilterData(perPage: 2));

    expect($result->perPage())->toBe(2)
        ->and($result->items())->toHaveCount(2);
});
