<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\ResetUserPasswordAction;
use App\DTOs\AdminResetPasswordData;
use App\Models\User;
use App\Notifications\Users\AdminResetPasswordNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('resets the user password, forces a password change and notifies the user', function () {
    Notification::fake();

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
        'must_change_password' => false,
    ]);

    $data = new AdminResetPasswordData(
        userId: $user->id,
        password: 'brand-new-password',
    );

    $result = app(ResetUserPasswordAction::class)->exec($data);

    expect($result)->toBeTrue();

    $user->refresh();

    expect(Hash::check('brand-new-password', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'must_change_password' => true,
    ]);

    Notification::assertSentTo(
        $user,
        AdminResetPasswordNotification::class,
        fn (AdminResetPasswordNotification $notification) => $notification->password === 'brand-new-password',
    );
});

it('throws when the user does not exist', function () {
    Notification::fake();

    $data = new AdminResetPasswordData(
        userId: 999999,
        password: 'whatever',
    );

    app(ResetUserPasswordAction::class)->exec($data);
})->throws(ModelNotFoundException::class);
