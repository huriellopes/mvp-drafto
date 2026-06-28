<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\ImpersonateUserAction;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ImpersonateUserAction::class);
});

it('returns false when a non super-admin tries to impersonate', function () {
    $actor = User::factory()->writer()->create();
    $target = User::factory()->create();

    $this->actingAs($actor);

    expect($this->action->exec($target))->toBeFalse();
    $this->assertDatabaseCount('impersonation_logs', 0);
});

it('returns false when a super-admin tries to impersonate themselves', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    expect($this->action->exec($admin))->toBeFalse();
    $this->assertDatabaseCount('impersonation_logs', 0);
});
