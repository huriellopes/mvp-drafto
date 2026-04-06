<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;
use App\Models\Post;

#[Signature('drafto:send-newsletter')]
#[Description('Dispara e-mails para inscritos com base em novos posts')]
class SendDailyNewsletterCommand extends Command
{
    public function handle(): void
    {
        $yesterday = now()->subDay();

        NewsletterSubscriber::query()
            ->with('category')
            ->chunk(100, function ($subscribers) use ($yesterday) {
                foreach ($subscribers as $subscriber) {
                    $posts = Post::query()
                        ->published()
                        ->where('created_at', '>=', $yesterday)
                        ->when($subscriber->category_id, fn($q) => $q->where('category_id', $subscriber->category_id))
                        ->limit(5)
                        ->get();

                    if ($posts->isNotEmpty()) {
                        SendNewsletterJob::dispatch(
                            $subscriber,
                            $posts->map(fn($post) => [
                                'title' => $post->title,
                                'excerpt' => $post->excerpt,
                                'slug' => $post->slug
                            ])->toArray(),
                            $subscriber->category?->name ?? 'Geral'
                        );
                    }
                }
            });

        $this->info('Newsletter processada e enfileirada com sucesso.');
    }
}
