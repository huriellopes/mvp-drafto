<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')
        ->name('login');
    Route::livewire('/register', 'auth.register')
        ->name('register');
    Route::livewire('/forgot-password', 'auth.forgot-password')
        ->name('password.request');
    Route::livewire('/reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('success', 'E-mail confirmado!');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::livewire('/email/verify', 'auth.verify-email-notice')
        ->name('verification.notice');

    Route::middleware(['check.verification.interval'])
        ->name('dashboard.')
        ->group(function () {
        Route::livewire('/dashboard', 'dashboard.index')
            ->name('index');

        Route::prefix('users')
            ->name('users.')
            ->group(function () {
                Route::livewire('/', 'dashboard.admin.users.user-index')
                    ->name('index');
            });

        Route::livewire('/profile', 'dashboard.profile.edit-profile')->name('profile');
    });
});

 Route::livewire('/@{username}', 'public.profile.show-profile')
    ->name('profile.show')
     ->middleware(['username.prefix'])
    ->where('username', '[a-z0-9._]+');

Route::redirect('/', '/dashboard');

// Route::middleware('auth')->group(function () {
//    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// require __DIR__ . '/auth.php';
