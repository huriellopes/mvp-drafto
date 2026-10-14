<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Console\Commands\MigrateSessionsToRedisCommand;
use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\ArrayInput;

function storeSession(string $id, string $data, int $lastActivity): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => base64_encode($data),
        'last_activity' => $lastActivity,
    ]);
}

beforeEach(function () {
    Cache::store('array')->flush();
});

it('copies active sessions keeping the same ids and payload', function () {
    storeSession('active-session', serialize(['_token' => 'abc', 'login_web_x' => 7]), now()->timestamp);

    $this->artisan('app:migrate-sessions-to-redis', ['--store' => 'array'])
        ->expectsOutputToContain('Sessões copiadas para o Redis: 1')
        ->assertExitCode(0);

    expect(Cache::store('array')->get('active-session'))
        ->toBe(serialize(['_token' => 'abc', 'login_web_x' => 7]));
});

it('skips expired sessions and corrupted payloads', function () {
    storeSession('expired', 'old', now()->subMinutes(config('session.lifetime') + 5)->timestamp);
    DB::table('sessions')->insert([
        'id' => 'corrupted', 'user_id' => null, 'ip_address' => null, 'user_agent' => null,
        'payload' => '***not-base64***', 'last_activity' => now()->timestamp,
    ]);

    $this->artisan('app:migrate-sessions-to-redis', ['--store' => 'array'])
        ->expectsOutputToContain('Sessões copiadas para o Redis: 0')
        ->assertExitCode(0);

    expect(Cache::store('array')->has('expired'))->toBeFalse()
        ->and(Cache::store('array')->has('corrupted'))->toBeFalse();
});

it('only counts on a dry run', function () {
    storeSession('active-session', 'data', now()->timestamp);

    $this->artisan('app:migrate-sessions-to-redis', ['--store' => 'array', '--dry-run' => true])
        ->expectsOutputToContain('seriam copiadas: 1')
        ->assertExitCode(0);

    expect(Cache::store('array')->has('active-session'))->toBeFalse();
});

it('targets the dedicated redis session connection with the cache prefix by default', function () {
    $command = $this->app->make(MigrateSessionsToRedisCommand::class);
    $command->setLaravel($this->app);
    $command->setInput(new ArrayInput([], $command->getDefinition()));

    /** @var Repository $repository */
    $repository = (fn () => $this->targetStore())->call($command);
    $store = $repository->getStore();

    expect($store)->toBeInstanceOf(RedisStore::class)
        ->and($store->getPrefix())->toBe(Cache::store('redis')->getStore()->getPrefix())
        ->and((fn () => $this->connection)->call($store))->toBe('session');
});
