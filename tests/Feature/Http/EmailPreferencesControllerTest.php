<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Support\Facades\URL;

function signedPreferencesUrl(User $user, string $type): string
{
    return URL::signedRoute('email.preferences.unsubscribe', [
        'user' => $user->id,
        'type' => $type,
    ]);
}

it('aborts when the signature is missing', function () {
    $user = User::factory()->create();

    $this->get(route('email.preferences.unsubscribe', [
        'user' => $user->id,
        'type' => 'reengagement',
    ]))->assertStatus(403);
});

it('unsubscribes a user from reengagement emails via a signed link', function () {
    $user = User::factory()->create(['wants_reengagement_emails' => true]);

    $this->get(signedPreferencesUrl($user, 'reengagement'))->assertOk();

    expect($user->fresh()->wants_reengagement_emails)->toBeFalse();
});

it('unsubscribes a user from product updates via a signed link', function () {
    $user = User::factory()->create(['wants_product_updates' => true]);

    $this->get(signedPreferencesUrl($user, 'product_updates'))->assertOk();

    expect($user->fresh()->wants_product_updates)->toBeFalse();
});

it('does not change anything for an unknown preference type', function () {
    $user = User::factory()->create([
        'wants_reengagement_emails' => true,
        'wants_product_updates' => true,
    ]);

    $this->get(signedPreferencesUrl($user, 'bogus'))->assertOk();

    expect($user->fresh()->wants_reengagement_emails)->toBeTrue()
        ->and($user->fresh()->wants_product_updates)->toBeTrue();
});
