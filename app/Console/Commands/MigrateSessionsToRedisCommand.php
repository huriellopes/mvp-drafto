<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Auth\CopyDatabaseSessionsAction;
use Illuminate\Cache\RedisStore;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Session\CacheBasedSessionHandler;
use Illuminate\Support\Facades\Cache;

#[Signature('app:migrate-sessions-to-redis
    {--connection=session : Conexão Redis de destino (config/database.php)}
    {--store=redis : Store de cache de destino (redis em produção)}
    {--dry-run : Só conta as sessões que seriam copiadas}')]
#[Description('Copia as sessões ativas da tabela sessions para o Redis antes de trocar SESSION_DRIVER (evita deslogar todo mundo).')]
final class MigrateSessionsToRedisCommand extends Command
{
    public function handle(CopyDatabaseSessionsAction $action): int
    {
        $lifetime = (int) config('session.lifetime', 120);
        $handler = new CacheBasedSessionHandler($this->targetStore(), $lifetime);
        $dryRun = (bool) $this->option('dry-run');

        $copied = $action->exec($handler, $lifetime, $dryRun);

        $this->info($dryRun
            ? "Sessões ativas que seriam copiadas: {$copied}."
            : "Sessões copiadas para o Redis: {$copied}. Agora troque SESSION_DRIVER=redis e recarregue o PHP-FPM.");

        return self::SUCCESS;
    }

    /**
     * Same store the redis session driver uses: cache prefix + the dedicated
     * "session" connection — built fresh so the app cache is not repointed.
     */
    private function targetStore(): Repository
    {
        $store = (string) $this->option('store');

        if ($store !== 'redis') {
            return Cache::store($store);
        }

        return Cache::repository(new RedisStore(
            resolve('redis'),
            (string) config('cache.prefix'),
            (string) $this->option('connection'),
        ));
    }
}
