<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessPostMediaAndSeoJob;
use App\Jobs\ProcessProfileMediaJob;
use App\Models\Post;
use App\Models\Profile;
use Illuminate\Console\Command;

/**
 * Reprocessa mídias já existentes (avatares, capas de perfil e capas de post)
 * aplicando o teto de resolução/WebP dos jobs de otimização.
 *
 * Uso pontual após o ajuste dos jobs: imagens antigas continuam gigantes até
 * serem reprocessadas. É idempotente — rodar de novo não degrada imagens já
 * otimizadas.
 */
final class OptimizeExistingMediaCommand extends Command
{
    protected $signature = 'media:optimize
        {--queue : Enfileira os jobs em vez de processar de forma síncrona}
        {--profiles : Processa apenas perfis (avatar/capa)}
        {--posts : Processa apenas capas de post}
        {--no-cache-clear : Não limpa o cache da aplicação ao final}';

    protected $description = 'Reotimiza (redimensiona + WebP) as mídias já existentes de perfis e posts.';

    public function handle(): int
    {
        $onlyProfiles = (bool) $this->option('profiles');
        $onlyPosts = (bool) $this->option('posts');
        $useQueue = (bool) $this->option('queue');

        // Sem flags específicas → processa tudo.
        $doProfiles = $onlyProfiles || !$onlyPosts;
        $doPosts = $onlyPosts || !$onlyProfiles;

        if ($doProfiles) {
            $this->processProfiles($useQueue);
        }

        if ($doPosts) {
            $this->processPosts($useQueue);
        }

        $this->afterProcessing($useQueue);

        $this->info('Concluído.');

        return self::SUCCESS;
    }

    /**
     * Reprocessar pode alterar caminhos de mídia (ex.: .jpg → .webp), tornando
     * obsoletas as listas cacheadas (home, exploradores, etc.). Limpamos o cache
     * da aplicação e lembramos do purge de CDN (nomes de arquivo são estáveis).
     */
    private function afterProcessing(bool $useQueue): void
    {
        if ($useQueue) {
            $this->newLine();
            $this->warn('Jobs enfileirados. Após a fila drenar, rode `php artisan cache:clear` e faça o purge do CDN/Cloudflare.');

            return;
        }

        if (!$this->option('no-cache-clear')) {
            $this->call('cache:clear');
            $this->info('Cache da aplicação limpo (caminhos de mídia atualizados).');
        }

        $this->newLine();
        $this->warn('Se as mídias são servidas via CDN/Cloudflare, faça o purge do cache: as imagens reprocessadas mantêm o mesmo nome de arquivo e continuarão sendo servidas em versão antiga até o purge.');
    }

    private function processProfiles(bool $useQueue): void
    {
        $query = Profile::query()
            ->where(function ($q): void {
                $q->whereNotNull('avatar_path')->orWhereNotNull('cover_path');
            });

        $total = $query->count();
        $this->info("Perfis com mídia: {$total}");
        $bar = $this->output->createProgressBar($total);

        $query->chunkById(100, function ($profiles) use ($useQueue, $bar): void {
            foreach ($profiles as $profile) {
                $useQueue
                    ? dispatch(new ProcessProfileMediaJob($profile))
                    : dispatch_sync(new ProcessProfileMediaJob($profile));
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    private function processPosts(bool $useQueue): void
    {
        $query = Post::query()
            ->whereNotNull('cover_image_path')
            ->where('cover_image_path', 'not like', 'http%');

        $total = $query->count();
        $this->info("Posts com capa local: {$total}");
        $bar = $this->output->createProgressBar($total);

        $query->chunkById(100, function ($posts) use ($useQueue, $bar): void {
            foreach ($posts as $post) {
                $useQueue
                    ? dispatch(new ProcessPostMediaAndSeoJob($post))
                    : dispatch_sync(new ProcessPostMediaAndSeoJob($post));
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }
}
