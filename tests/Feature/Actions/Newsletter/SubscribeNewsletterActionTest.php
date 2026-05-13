<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Newsletter;

use App\Actions\Public\SubscribeNewsletterAction;
use App\DTOs\Public\NewsletterData;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscriber;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->action = app(SubscribeNewsletterAction::class);
});

it('creates a new subscriber and sends verification email', function () {
    $data = new NewsletterData(
        email: 'new@example.com',
        categoryId: null,
    );

    $this->action->exec($data);

    $subscriber = NewsletterSubscriber::where('email', 'new@example.com')->first();

    expect($subscriber)->not->toBeNull()
        ->and($subscriber->verified_at)->toBeNull()
        ->and($subscriber->verification_token)->not->toBeNull();

    Mail::assertSent(NewsletterVerificationMail::class, function ($mail) use ($subscriber) {
        return $mail->hasTo('new@example.com') && $mail->subscriber->id === $subscriber->id;
    });
});

it('associates a category if provided during new subscription', function () {
    $category = PostCategory::factory()->create();
    $data = new NewsletterData(
        email: 'category@example.com',
        categoryId: $category->id,
    );

    $this->action->exec($data);

    $subscriber = NewsletterSubscriber::where('email', 'category@example.com')->first();

    expect($subscriber->categories)->toHaveCount(1)
        ->and($subscriber->categories->first()->id)->toBe($category->id);
});

it('does not send verification email if subscriber is already verified', function () {
    $subscriber = NewsletterSubscriber::factory()->create([
        'email' => 'verified@example.com',
        'verified_at' => now(),
    ]);

    $data = new NewsletterData(
        email: 'verified@example.com',
        categoryId: null,
    );

    $this->action->exec($data);

    Mail::assertNotSent(NewsletterVerificationMail::class);
});

it('updates categories for existing verified subscriber without re-verifying', function () {
    $subscriber = NewsletterSubscriber::factory()->create([
        'email' => 'existing@example.com',
        'verified_at' => now(),
    ]);

    $category = PostCategory::factory()->create();

    $data = new NewsletterData(
        email: 'existing@example.com',
        categoryId: $category->id,
    );

    $this->action->exec($data);

    expect($subscriber->refresh()->verified_at)->not->toBeNull()
        ->and($subscriber->categories)->toHaveCount(1);

    Mail::assertNotSent(NewsletterVerificationMail::class);
});
