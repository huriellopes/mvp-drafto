<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('drafto:send-newsletter')
    ->dailyAt('08:00');

Schedule::command('drafto:sync-views')
    ->hourly();

Schedule::command('app:expire-trials')
    ->dailyAt('00:00')
    ->runInBackground();

Schedule::command('app:archive-post-views')
    ->dailyAt('03:00')
    ->onOneServer();

Schedule::command('seo:generate-sitemap')
    ->dailyAt('02:00')
    ->onOneServer();
