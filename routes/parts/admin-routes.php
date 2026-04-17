<?php

declare(strict_types=1);

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
        Route::livewire('/subscriptions', 'dashboard.admin.subscriptions.subscription-index')
            ->name('subscriptions.index');
        Route::livewire('/modules', 'dashboard.admin.modules.module-index')
            ->name('modules.index');
        Route::livewire('/views', 'dashboard.admin.post-views.post-view-index')
            ->name('posts.views');
    });
