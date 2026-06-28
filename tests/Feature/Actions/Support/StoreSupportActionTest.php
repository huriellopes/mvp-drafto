<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Support;

use App\Actions\Support\StoreSupportAction;
use App\DTOs\SupportData;
use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportMessageReceivedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    Http::fake();
    $this->action = app(StoreSupportAction::class);
});

it('creates a pending support ticket for the user', function () {
    $user = User::factory()->create();

    $support = $this->action->exec($user, new SupportData(
        subject: 'Login issue',
        message: 'Cannot log in',
    ));

    expect($support)->toBeInstanceOf(Support::class)
        ->and($support->user_id)->toBe($user->id)
        ->and($support->subject)->toBe('Login issue')
        ->and($support->status)->toBe(SupportStatusEnum::PENDING);

    $this->assertDatabaseHas('supports', ['id' => $support->id]);
});

it('notifies the support mailbox and admins', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->action->exec($user, new SupportData(
        subject: 'Question',
        message: 'How does this work?',
    ));

    Notification::assertSentTo($admin, SupportMessageReceivedNotification::class);
    Notification::assertSentOnDemand(SupportMessageReceivedNotification::class);
});
