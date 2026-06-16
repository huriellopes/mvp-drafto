<?php

declare(strict_types=1);

Schedule::command('posts:publish-scheduled')
    ->everyMinute();

Schedule::command('drafto:send-newsletter')
    ->dailyAt('08:00');

Schedule::command('drafto:sync-views')
    ->hourly();

Schedule::command('app:archive-post-views')
    ->dailyAt('03:00')
    ->onOneServer();

Schedule::command('app:cleanup-exports')
    ->twiceMonthly(1, 16, '03:00')
    ->onOneServer();

Schedule::command('app:generate-missing-excerpts')
    ->cron('0 0 */3 * *')
    ->onOneServer();

Schedule::command('seo:generate-sitemap')
    ->dailyAt('02:00')
    ->onOneServer();

Schedule::command('users:reengage')
    ->dailyAt('09:00')
    ->onOneServer();
