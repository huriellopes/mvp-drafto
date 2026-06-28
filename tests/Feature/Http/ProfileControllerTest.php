<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Controllers\ProfileController;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ProfileController is not wired to any route in routes/*.php, so we exercise
// its methods directly with prepared requests. The update() action redirects to
// a `profile.edit` named route, which we register here for the redirect to resolve.
beforeEach(function () {
    Route::get('/profile', fn () => 'edit')->name('profile.edit');
    app('router')->getRoutes()->refreshNameLookups();
});

it('updates the user name and email', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    Auth::login($user);

    $request = ProfileUpdateRequest::create('/', 'PATCH');
    $request->setUserResolver(fn () => $user);
    $request->merge(['name' => 'New Name', 'email' => 'new@example.com']);
    $request->setValidator(validator(
        ['name' => 'New Name', 'email' => 'new@example.com'],
        $request->rules(),
    ));

    (new ProfileController())->update($request);

    expect(session('status'))->toBe('profile-updated');

    $fresh = $user->fresh();
    expect($fresh->name)->toBe('New Name')
        ->and($fresh->email)->toBe('new@example.com')
        ->and($fresh->email_verified_at)->toBeNull();
});

it('keeps email verification when the email is unchanged', function () {
    $user = User::factory()->create([
        'email' => 'same@example.com',
        'email_verified_at' => now(),
    ]);

    Auth::login($user);

    $request = ProfileUpdateRequest::create('/', 'PATCH');
    $request->setUserResolver(fn () => $user);
    $request->merge(['name' => 'Changed', 'email' => 'same@example.com']);
    $request->setValidator(validator(
        ['name' => 'Changed', 'email' => 'same@example.com'],
        $request->rules(),
    ));

    (new ProfileController())->update($request);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('deletes the account when the correct password is given', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->actingAs($user);

    $request = Request::create('/', 'DELETE', ['password' => 'password']);
    $request->setUserResolver(fn () => $user);
    $request->setLaravelSession($this->app['session']->driver());

    $response = (new ProfileController())->destroy($request);

    expect($response->getTargetUrl())->toBe(url('/'));
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    expect(Auth::check())->toBeFalse();
});

it('renders the edit view', function () {
    $user = User::factory()->create();

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $view = (new ProfileController())->edit($request);

    expect($view->name())->toBe('profile.edit')
        ->and($view->getData()['user']->id)->toBe($user->id);
});
