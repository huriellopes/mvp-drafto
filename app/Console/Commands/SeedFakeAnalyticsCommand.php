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
            $count = rand(5, 20);

            for ($j = 0; $j < $count; $j++) {
                SiteView::create([
                    'url' => $urls[array_rand($urls)],
                    'ip_address' => '127.0.0.' . rand(1, 255),
                    'session_id' => 'session_' . rand(1, 100),
                    'user_id' => rand(0, 1) ? 1 : null,
                    'search_query' => rand(0, 10) > 7 ? $searches[array_rand($searches)] : null,
                    'duration' => rand(10, 300),
                    'viewed_at' => $date->copy()->subMinutes(rand(0, 1440)),
                ]);
            }
        }

        $this->info('Success! Seeded ' . SiteView::count() . ' records.');
    }
}
