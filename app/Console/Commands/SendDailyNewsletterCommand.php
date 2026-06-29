<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('drafto:send-newsletter')]
#[Description('Dispara e-mails para inscritos com base em novos posts')]
class SendDailyNewsletterCommand extends Command
{
    public function handle(): void
    {
        $yesterday = now()->subDay();

        // Sênior: Pré-busca todos os posts publicados nas últimas 24h para evitar queries dentro do loop
        $recentPosts = Post::query()
            ->published()
            ->where('created_at', '>=', $yesterday)
            ->get();

        if ($recentPosts->isEmpty()) {
            $this->info('Nenhum post novo nas últimas 24h. Newsletter ignorada.');

            return;
        }

        // Mapeia os posts por categoria para acesso rápido O(1)
        $postsByCategory = $recentPosts->groupBy('category_id');

        NewsletterSubscriber::query()
            ->whereNotNull('verified_at')
            ->with('categories')
            ->chunk(100, function ($subscribers) use ($postsByCategory) {
                foreach ($subscribers as $subscriber) {
                    $postsToSend = collect();

                    // Se o usuário não tem categorias específicas, enviamos os posts mais recentes gerais
                    if ($subscriber->categories->isEmpty()) {
                        $postsToSend = $postsByCategory->flatten()->take(5);
                    } else {
                        // Busca posts das categorias de interesse dele
                        foreach ($subscriber->categories as $category) {
                            $categoryPosts = $postsByCategory->get($category->id, collect());
                            $postsToSend = $postsToSend->concat($categoryPosts);
                        }
                        $postsToSend = $postsToSend->unique('id')->take(5);
                    }

                    if ($postsToSend->isNotEmpty()) {
                        dispatch(new SendNewsletterJob($subscriber, $postsToSend->map(fn ($post) => [
                            'title' => $post->title,
                            'excerpt' => $post->excerpt,
                            'slug' => $post->slug,
                        ])->toArray(), $subscriber->categories->isEmpty() ? 'Geral' : 'Seus Interesses'));
                    }
                }
            });

        $this->info('Newsletter processada com sucesso.');
    }
}
