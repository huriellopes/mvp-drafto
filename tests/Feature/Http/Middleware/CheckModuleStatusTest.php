<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Enums\ModuleEnum;
use App\Http\Middleware\CheckModuleStatus;
use App\Models\Module;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('aborts with 404 when the module is globally disabled', function () {
    Module::where('slug', ModuleEnum::LINK_SHORTENER->value)
        ->update(['is_enabled' => false]);

    $middleware = new CheckModuleStatus;

    $middleware->handle(
        Request::create('/dashboard/encurtador'),
        fn () => response('ok'),
        ModuleEnum::LINK_SHORTENER->value,
    );
})->throws(HttpException::class);

it('passes the request through when the module is enabled', function () {
    $middleware = new CheckModuleStatus;

    $response = $middleware->handle(
        Request::create('/dashboard/encurtador'),
        fn () => response('ok'),
        ModuleEnum::LINK_SHORTENER->value,
    );

    expect($response->getContent())->toBe('ok');
});
