<?php

declare(strict_types=1);

Schedule::command('posts:publish-scheduled')
    ->everyMinute();

Schedule::command('drafto:send-newsletter')
    ->dailyAt('08:00');

Schedule::command('drafto:daily-summary')
    ->dailyAt('08:05');

Schedule::command('app:prune-logs')
    ->dailyAt('04:00')
    ->onOneServer();

Schedule::command('drafto:sync-views')
    ->hourly();

Schedule::command('app:archive-post-views')
    ->dailyAt('03:00')
    ->onOneServer();

Schedule::command('app:purge-site-views')
    ->dailyAt('03:15')
    ->onOneServer();

Schedule::command('app:cleanup-exports')
    ->twiceMonthly(1, 16, '03:00')
    ->onOneServer();

Schedule::command('app:generate-missing-excerpts')
    ->cron('0 0 */3 * *')
    ->onOneServer();

Schedule::command('users:reengage')
    ->dailyAt('09:00')
    ->onOneServer();
