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
        {--posts : Processa apenas capas de post}';

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

        $this->info('Concluído.');

        return self::SUCCESS;
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
                    ? ProcessProfileMediaJob::dispatch($profile)
                    : ProcessProfileMediaJob::dispatchSync($profile);
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
                    ? ProcessPostMediaAndSeoJob::dispatch($post)
                    : ProcessPostMediaAndSeoJob::dispatchSync($post);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }
}
