<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:generate-missing-excerpts')]
#[Description('Generate missing excerpts for posts and articles from their content')]
class GenerateMissingExcerptsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting excerpt generation...');

        $posts = Post::query()
            ->published()
            ->where(function ($query) {
                $query->whereNull('excerpt')
                    ->orWhere('excerpt', '');
            })
            ->where('content', '!=', '')
            ->whereNotNull('content')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No published posts found with missing excerpts.');
            return;
        }

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        $updatedCount = 0;
        foreach ($posts as $post) {
            $cleanContent = strip_tags((string) $post->content);
            
            if (empty(trim($cleanContent))) {
                $bar->advance();
                continue;
            }

            $excerpt = Str::limit($cleanContent, 160);

            $post->update(['excerpt' => $excerpt]);
            $updatedCount++;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated excerpts for {$updatedCount} posts.");
    }
}
