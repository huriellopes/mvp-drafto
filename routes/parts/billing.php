<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('faturamento')
    ->name('billing.')
    ->middleware(['module:subscription', 'module.access:subscription'])
    ->group(function () {
        Route::livewire('/', 'dashboard.billing.subscription-manager')->name('index');
        Route::livewire('/planos', 'dashboard.billing.plans-index')->name('plans');

        Route::get('/portal', function (Request $request) {
            return $request->user()->redirectToBillingPortal(route('dashboard.account'));
        })->name('portal');
    });
