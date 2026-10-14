<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('lets only super admins view Horizon', function () {
    $admin = User::factory()->superAdmin()->create();
    $writer = User::factory()->create();

    expect(Gate::forUser($admin)->allows('viewHorizon'))->toBeTrue()
        ->and(Gate::forUser($writer)->allows('viewHorizon'))->toBeFalse();
});

it('denies guests', function () {
    expect(Gate::allows('viewHorizon'))->toBeFalse();
});

it('schedules the Horizon metrics snapshot', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('horizon:snapshot')
        ->assertExitCode(0);
});
