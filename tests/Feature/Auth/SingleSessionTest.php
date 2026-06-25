<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

function fakeSession(string $id, int $userId): array
{
    return [
        'id' => $id,
        'user_id' => $userId,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'payload' => 'x',
        'last_activity' => time(),
    ];
}

it('ends other sessions of the same user on login', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $other = User::factory()->create();

    DB::table('sessions')->insert([
        fakeSession('device-a', $user->id),
        fakeSession('device-b', $user->id),
        fakeSession('other-user-device', $other->id),
    ]);

    event(new Login('web', $user, false));

    // As sessões do usuário em outras máquinas foram encerradas.
    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
    // A sessão de outro usuário permanece intacta.
    expect(DB::table('sessions')->where('user_id', $other->id)->count())->toBe(1);
});

it('rotates the remember token on a non-remember login (kills stale remember cookies)', function () {
    $user = User::factory()->create(['remember_token' => 'old-token-value']);

    event(new Login('web', $user, false));

    expect($user->fresh()->remember_token)->not->toBe('old-token-value');
});

it('keeps the remember token on a remember-me login (Auth already cycled it)', function () {
    $user = User::factory()->create(['remember_token' => 'keep-this-token']);

    event(new Login('web', $user, true));

    expect($user->fresh()->remember_token)->toBe('keep-this-token');
});
