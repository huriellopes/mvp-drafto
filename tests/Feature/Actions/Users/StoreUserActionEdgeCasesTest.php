<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\StoreUserAction;
use App\DTOs\SaveUserData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->action = app(StoreUserAction::class);
});

it('detects writer role when provided as a string value', function () {
    Event::fake();

    $data = SaveUserData::from([
        'name' => 'String Role User',
        'email' => 'string-role@example.com',
        'password' => 'password123',
        'role' => RoleEnum::WRITER->value, // passes a string, exercising line 24 branch
        'status' => UserStatusEnum::ACTIVE,
        'send_welcome_email' => true,
    ]);

    $user = $this->action->exec($data);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->profile)->not->toBeNull();

    Event::assertDispatched(Registered::class);
});

it('does not dispatch the registered event when welcome email is disabled', function () {
    Event::fake();

    $data = SaveUserData::from([
        'name' => 'No Welcome',
        'email' => 'no-welcome@example.com',
        'password' => 'password123',
        'role' => RoleEnum::READER->value,
        'status' => UserStatusEnum::ACTIVE,
        'send_welcome_email' => false,
    ]);

    $this->action->exec($data);

    Event::assertNotDispatched(Registered::class);
});
