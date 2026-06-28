<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

function profileUpdateRules(User $user): array
{
    $request = new ProfileUpdateRequest();
    $request->setUserResolver(fn () => $user);

    return $request->rules();
}

it('passes validation with a valid name and email', function () {
    $user = User::factory()->create();

    $validator = Validator::make(
        ['name' => 'Jane Doe', 'email' => 'jane@example.com'],
        profileUpdateRules($user),
    );

    expect($validator->passes())->toBeTrue();
});

it('requires name and email', function () {
    $user = User::factory()->create();

    $validator = Validator::make([], profileUpdateRules($user));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('name', 'email');
});

it('rejects a non lowercase email', function () {
    $user = User::factory()->create();

    $validator = Validator::make(
        ['name' => 'Jane', 'email' => 'Jane@Example.com'],
        profileUpdateRules($user),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

it('rejects an email already used by another user', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $validator = Validator::make(
        ['name' => 'Jane', 'email' => 'taken@example.com'],
        profileUpdateRules($user),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

it('allows the user to keep their own email', function () {
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $validator = Validator::make(
        ['name' => 'Jane', 'email' => 'mine@example.com'],
        profileUpdateRules($user),
    );

    expect($validator->passes())->toBeTrue();
});
