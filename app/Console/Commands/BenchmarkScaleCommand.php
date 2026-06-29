<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Posts\ToggleLikeAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('benchmark:scale {--users=100} {--likes=500}')]
#[Description('Simula carga de interações para testar performance e concorrência.')]
final class BenchmarkScaleCommand extends Command
{
    public function handle(): int
    {
        $userCount = (int) $this->option('users');
        $likeCount = (int) $this->option('likes');

        $this->info("Iniciando Benchmark: {$userCount} usuários, {$likeCount} likes simulados...");

        $post = Post::first() ?? Post::factory()->create();
        $users = User::factory()->count($userCount)->create();

        $start = microtime(true);
        $errors = 0;

        $bar = $this->output->createProgressBar($likeCount);
        $bar->start();

        for ($i = 0; $i < $likeCount; $i++) {
            try {
                $user = $users->random();
                resolve(ToggleLikeAction::class)
                    ->exec(
                        user: $user,
                        post: $post,
                    );
            } catch (Throwable) {
                $errors++;
            }
            $bar->advance();
        }

        $bar->finish();
        $end = microtime(true);
        $time = $end - $start;

        $this->newLine(2);
        $this->table(
            ['Métrica', 'Resultado'],
            [
                ['Tempo Total', number_format($time, 2) . 's'],
                ['Likes/Segundo', number_format($likeCount / $time, 2)],
                ['Erros (Deadlocks/Timeout)', $errors],
                ['Post Likes Final', $post->fresh()->likes_count],
            ],
        );

        $this->info('Limpando os usuários criados...');
        $users->each->delete();

        return self::SUCCESS;
    }
}
