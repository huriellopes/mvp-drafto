<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\ModuleEnum;
use App\Models\Module;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('casts slug to the enum, is_enabled to bool and settings to array', function () {
    $module = Module::where('slug', ModuleEnum::SUPPORT->value)->firstOrFail();
    $module->update(['settings' => ['foo' => 'bar']]);

    expect($module->slug)->toBeInstanceOf(ModuleEnum::class)
        ->and($module->is_enabled)->toBeBool()
        ->and($module->settings)->toBe(['foo' => 'bar']);
});

it('uses the slug as the route key', function () {
    expect((new Module)->getRouteKeyName())->toBe('slug');
});

it('reads a setting via getSetting with a default fallback', function () {
    $module = Module::where('slug', ModuleEnum::SUPPORT->value)->firstOrFail();
    $module->update(['settings' => ['limit' => 10]]);

    expect($module->getSetting('limit'))->toBe(10)
        ->and($module->getSetting('missing', 'fallback'))->toBe('fallback');
});

it('reports enabled status accepting an enum', function () {
    expect(Module::isEnabled(ModuleEnum::SUPPORT))->toBeTrue();
});

it('reports enabled status accepting a string slug', function () {
    expect(Module::isEnabled(ModuleEnum::SUPPORT->value))->toBeTrue();
});

it('reports disabled module as not enabled', function () {
    $module = Module::where('slug', ModuleEnum::SUPPORT->value)->firstOrFail();
    $module->update(['is_enabled' => false]);
    Cache::flush();

    expect(Module::isEnabled(ModuleEnum::SUPPORT))->toBeFalse();
});
