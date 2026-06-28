<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Support;

use App\Actions\Support\UpdateSupportAction;
use App\DTOs\SupportData;
use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportResponseNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->action = app(UpdateSupportAction::class);
});

it('updates status and notifies the user when a response is given', function () {
    $admin = User::factory()->superAdmin()->create();
    $owner = User::factory()->create();
    $support = Support::create([
        'user_id' => $owner->id,
        'subject' => 'Help',
        'message' => 'Please assist',
        'status' => SupportStatusEnum::PENDING,
    ]);

    $updated = $this->action->exec($admin, $support, new SupportData(
        subject: 'Help',
        message: 'Please assist',
        admin_response: 'Here is the fix',
        status: SupportStatusEnum::RESOLVED->value,
    ));

    expect($updated->status)->toBe(SupportStatusEnum::RESOLVED)
        ->and($updated->admin_response)->toBe('Here is the fix')
        ->and($updated->responded_by)->toBe($admin->id)
        ->and($updated->responded_at)->not->toBeNull();

    Notification::assertSentTo($owner, SupportResponseNotification::class);
});

it('does not notify the user when there is no admin response', function () {
    $admin = User::factory()->superAdmin()->create();
    $owner = User::factory()->create();
    $support = Support::create([
        'user_id' => $owner->id,
        'subject' => 'Help',
        'message' => 'Please assist',
        'status' => SupportStatusEnum::PENDING,
    ]);

    $this->action->exec($admin, $support, new SupportData(
        subject: 'Help',
        message: 'Please assist',
        status: SupportStatusEnum::IN_PROGRESS->value,
    ));

    Notification::assertNothingSent();
});
