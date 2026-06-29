<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SiteView;
use Illuminate\Console\Command;

final class SeedFakeAnalyticsCommand extends Command
{
    protected $signature = 'app:seed-fake-analytics';

    protected $description = 'Seed the site_views table with fake data for testing';

    public function handle(): void
    {
        $this->info('Seeding fake analytics data...');

        $urls = [
            'http://localhost/',
            'http://localhost/posts/post-1',
            'http://localhost/posts/post-2',
            'http://localhost/@admin',
            'http://localhost/explore',
        ];

        $searches = ['laravel', 'php', 'drafto', 'tailwind', 'livewire'];

        // Last 45 days
        for ($i = 0; $i < 45; $i++) {
            $date = now()->subDays($i);
            $count = random_int(5, 20);

            for ($j = 0; $j < $count; $j++) {
                SiteView::create([
                    'url' => $urls[array_rand($urls)],
                    'ip_address' => '127.0.0.' . random_int(1, 255),
                    'session_id' => 'session_' . random_int(1, 100),
                    'user_id' => random_int(0, 1) !== 0 ? 1 : null,
                    'search_query' => random_int(0, 10) > 7 ? $searches[array_rand($searches)] : null,
                    'duration' => random_int(10, 300),
                    'viewed_at' => $date->copy()->subMinutes(random_int(0, 1440)),
                ]);
            }
        }

        $this->info('Success! Seeded ' . SiteView::count() . ' records.');
    }
}
