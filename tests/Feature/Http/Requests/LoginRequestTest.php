<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

function makeLoginRequest(array $data): LoginRequest
{
    $request = LoginRequest::create('/login', 'POST', $data);
    $request->setContainer(app());

    return $request;
}

it('authorizes the request', function () {
    expect((new LoginRequest())->authorize())->toBeTrue();
});

it('requires email and password', function () {
    $validator = Validator::make([], (new LoginRequest())->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('email', 'password');
});

it('rejects an invalid email format', function () {
    $validator = Validator::make(
        ['email' => 'not-an-email', 'password' => 'secret'],
        (new LoginRequest())->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'auth@example.com',
        'password' => bcrypt('password'),
    ]);

    $request = makeLoginRequest(['email' => 'auth@example.com', 'password' => 'password']);

    $request->authenticate();

    expect(auth()->id())->toBe($user->id);
});

it('throws a validation exception on wrong credentials and hits the rate limiter', function () {
    User::factory()->create(['email' => 'auth@example.com', 'password' => bcrypt('password')]);

    $request = makeLoginRequest(['email' => 'auth@example.com', 'password' => 'wrong']);

    expect(fn () => $request->authenticate())->toThrow(ValidationException::class);
    expect(RateLimiter::attempts($request->throttleKey()))->toBe(1);
});

it('blocks authentication after too many attempts', function () {
    $request = makeLoginRequest(['email' => 'flood@example.com', 'password' => 'x']);

    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit($request->throttleKey());
    }

    expect(fn () => $request->authenticate())->toThrow(ValidationException::class);
});

it('builds a throttle key from email and ip', function () {
    $request = makeLoginRequest(['email' => 'KEY@Example.com', 'password' => 'x']);

    expect($request->throttleKey())->toContain('key@example.com');
});
