<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Posts\PublishScheduledPostsAction;
use App\Enums\ModuleEnum;
use App\Models\Module;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:publish-scheduled')]
#[Description('Publica posts que foram agendados para o momento atual.')]
class PublishScheduledPostsCommand extends Command
{
    public function handle(): void
    {
        if (!Module::isEnabled(ModuleEnum::POST_SCHEDULER)) {
            $this->warn('O módulo de agendamento de postagens está desativado.');
            return;
        }

        $this->info('Iniciando publicação de posts agendados...');

        $count = app(PublishScheduledPostsAction::class)
            ->exec();

        $this->info("Sucesso! {$count} posts foram publicados.");
    }
}
