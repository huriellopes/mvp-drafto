<?php

declare(strict_types=1);

use Spatie\Health\Http\Controllers\HealthCheckResultsController;

Route::prefix('admin')
    ->middleware(['can:admin'])
    ->name('admin.')
    ->group(function () {
        Route::livewire('/newsletter', 'dashboard.admin.newsletter.newsletter-index')
            ->name('newsletter.index');
        Route::livewire('/reports', 'dashboard.admin.reports.report-index')
            ->name('reports.index');
        Route::livewire('/users', 'dashboard.admin.users.user-index')
            ->name('users.index');
        Route::livewire('/modulos', 'dashboard.admin.modules.module-index')->name('modules.index');
        Route::livewire('/suporte', 'dashboard.admin.support.support-index')->name('support.index');

        Route::livewire('/views', 'dashboard.admin.post-views.post-view-index')
            ->name('posts.views');
        Route::livewire('/logs', 'dashboard.admin.audit-log-index')
            ->name('logs.index');

        Route::get('/health', HealthCheckResultsController::class)
            ->name('health');
    });
