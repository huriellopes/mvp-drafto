<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public;

use App\Livewire\Public\NewsletterForm;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscriber;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
});

it('renders successfully', function () {
    Livewire::test(NewsletterForm::class)->assertOk();
});

it('requires an email', function () {
    Livewire::test(NewsletterForm::class)
        ->set('email', '')
        ->call('subscribe')
        ->assertHasErrors(['email' => 'required']);
});

it('rejects an invalid email', function () {
    Livewire::test(NewsletterForm::class)
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email' => 'email']);
});

it('rejects an already-subscribed email', function () {
    NewsletterSubscriber::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(NewsletterForm::class)
        ->set('email', 'taken@example.com')
        ->call('subscribe')
        ->assertHasErrors(['email' => 'unique']);
});

it('subscribes a new email and resets the field', function () {
    Livewire::test(NewsletterForm::class)
        ->set('email', 'new@example.com')
        ->call('subscribe')
        ->assertHasNoErrors()
        ->assertSet('email', '');

    expect(NewsletterSubscriber::where('email', 'new@example.com')->exists())->toBeTrue();
    Mail::assertSent(NewsletterVerificationMail::class);
});

it('subscribes with a category interest', function () {
    $category = PostCategory::factory()->create();

    Livewire::test(NewsletterForm::class)
        ->set('email', 'interested@example.com')
        ->set('categoryId', $category->id)
        ->call('subscribe')
        ->assertHasNoErrors();

    $subscriber = NewsletterSubscriber::where('email', 'interested@example.com')->first();

    expect($subscriber)->not->toBeNull()
        ->and($subscriber->categories()->where('post_category_id', $category->id)->exists())->toBeTrue();
});
