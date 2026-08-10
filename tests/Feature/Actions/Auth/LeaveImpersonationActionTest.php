<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\LeaveImpersonationAction;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->action = app(LeaveImpersonationAction::class);
});

it('returns false when there is no active impersonation', function () {
    expect($this->action->exec())->toBeFalse();
});

it('returns false when the impersonator no longer exists', function () {
    Session::put('impersonator_id', 999999);

    expect($this->action->exec())->toBeFalse();
});

it('logs back in as the admin, closes the audit log and clears the flag', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create();

    $log = ImpersonationLog::create([
        'impersonator_id' => $admin->id,
        'impersonated_id' => $target->id,
        'started_at' => now()->subMinute(),
        'ended_at' => null,
    ]);

    Auth::login($target);
    Session::put('impersonator_id', $admin->id);

    $result = $this->action->exec();

    expect($result)->toBeTrue()
        ->and(Auth::id())->toBe($admin->id)
        ->and(Session::has('impersonator_id'))->toBeFalse()
        ->and($log->fresh()->ended_at)->not->toBeNull();
});

it('regenerates the session id when leaving impersonation', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create();

    ImpersonationLog::create([
        'impersonator_id' => $admin->id,
        'impersonated_id' => $target->id,
        'started_at' => now()->subMinute(),
        'ended_at' => null,
    ]);

    Auth::login($target);
    Session::put('impersonator_id', $admin->id);

    $sessionIdBefore = session()->getId();

    $this->action->exec();

    expect(session()->getId())->not->toBe($sessionIdBefore);
});
