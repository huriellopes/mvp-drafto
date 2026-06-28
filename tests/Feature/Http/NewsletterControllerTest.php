<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\URL;

it('unsubscribes a subscriber via a signed link', function () {
    $subscriber = NewsletterSubscriber::factory()->create(['email' => 'reader@example.com']);

    $url = URL::signedRoute('newsletter.unsubscribe', ['email' => $subscriber->email]);

    $this->get($url)->assertOk();

    $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'reader@example.com']);
});

it('still renders when the email is not subscribed', function () {
    $url = URL::signedRoute('newsletter.unsubscribe', ['email' => 'ghost@example.com']);

    $this->get($url)->assertOk();
});

it('aborts unsubscribe when the signature is missing', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $this->get(route('newsletter.unsubscribe', ['email' => $subscriber->email]))
        ->assertStatus(403);
});

it('verifies a subscriber with a matching token', function () {
    $subscriber = NewsletterSubscriber::factory()->create([
        'email' => 'verify@example.com',
        'verification_token' => 'token-123',
        'verified_at' => null,
    ]);

    $this->get(route('newsletter.verify', [
        'email' => $subscriber->email,
        'token' => 'token-123',
    ]))->assertOk();

    $fresh = $subscriber->fresh();
    expect($fresh->verified_at)->not->toBeNull()
        ->and($fresh->verification_token)->toBeNull();
});

it('returns 404 when verifying with a wrong token', function () {
    $subscriber = NewsletterSubscriber::factory()->create([
        'email' => 'verify@example.com',
        'verification_token' => 'right-token',
    ]);

    $this->get(route('newsletter.verify', [
        'email' => $subscriber->email,
        'token' => 'wrong-token',
    ]))->assertStatus(404);
});
