<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

final class SyncPostViews extends Command
{
    protected $signature = 'drafto:sync-views';

    protected $description = 'Sync buffered post views from Redis to Database';

    public function handle(): void
    {
        $keys = Redis::keys('post_views_buffer:*');

        if (empty($keys)) {
            $this->info('No buffered views to sync.');

            return;
        }

        $this->info('Syncing ' . count($keys) . ' posts views...');

        foreach ($keys as $key) {
            // Redis::keys retorna o prefixo completo do Redis configurado no Laravel
            // Removemos o prefixo para pegar o ID
            $postId = (int) str_replace(config('database.redis.options.prefix') . 'post_views_buffer:', '', $key);
            $views = (int) Redis::getdel($key);

            if ($views > 0) {
                Post::where('id', $postId)->increment('views_count', $views);
            }
        }

        $this->info('Sync completed successfully.');
    }
}
