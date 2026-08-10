<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\ImpersonateUserAction;
use App\Models\User;
use Illuminate\Support\Facades\Session;

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

it('returns false when a super-admin tries to impersonate another super-admin', function () {
    $actor = User::factory()->superAdmin()->create();
    $target = User::factory()->superAdmin()->create();

    $this->actingAs($actor);

    expect($this->action->exec($target))->toBeFalse();

    $this->assertDatabaseCount('impersonation_logs', 0);
    expect(Session::has('impersonator_id'))->toBeFalse();
    expect(auth()->id())->toBe($actor->id);
});

it('regenerates the session id when a super-admin successfully impersonates a non super-admin', function () {
    $actor = User::factory()->superAdmin()->create();
    $target = User::factory()->writer()->create();

    $this->actingAs($actor);

    $sessionIdBefore = session()->getId();

    expect($this->action->exec($target))->toBeTrue();

    expect(session()->getId())->not->toBe($sessionIdBefore)
        ->and(auth()->id())->toBe($target->id)
        ->and(Session::get('impersonator_id'))->toBe($actor->id);

    $this->assertDatabaseHas('impersonation_logs', [
        'impersonator_id' => $actor->id,
        'impersonated_id' => $target->id,
    ]);
});
