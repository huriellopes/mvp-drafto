<?php

declare(strict_types=1);

use App\Actions\Auth\VerifyEmailAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')->name('login');
    Route::livewire('/login/two-factor', 'auth.two-factor-challenge')->name('auth.two-factor');
    Route::livewire('/register', 'auth.register')->name('register');
    Route::livewire('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::livewire('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/logout', 'auth.logout')->name('logout');

    // Verificação de E-mail
    Route::get('/email/verify/{id}/{hash}', function (Request $request) {
        $result = app(VerifyEmailAction::class)->exec($request);

        if ($result) {
            session()->flash('success', 'E-mail verificado com sucesso!');
        } else {
            session()->flash('error', 'O link de verificação é inválido ou expirou.');
        }

        return redirect()->route('dashboard.index');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::livewire('/email/verify', 'auth.verify-email-notice')->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});
